package services

import (
	"bytes"
	"dwd/models"
	"dwd/utils"
	"encoding/json"
	"fmt"
	"io"
	"io/ioutil"
	"net/http"
	"os"
	"strings"
	"time"
)

var rules models.CustomRules

func LoadCustomRules(path string) error {
	file, err := os.ReadFile(path)
	if err != nil {
		return err
	}
	return json.Unmarshal(file, &rules)
}

func GetSecrets(tenantID string) (models.TenantData, error) {
	url := "http://dwd:5000/secrets" // endpoint without tenantID in URL
	// Prepare JSON body
	payload := map[string]string{"tenant_id": tenantID}
	jsonBody, err := json.Marshal(payload)
	if err != nil {
		return models.TenantData{}, fmt.Errorf("failed to marshal JSON: %w", err)
	}

	// Build POST request
	req, err := http.NewRequest("GET", url, bytes.NewBuffer(jsonBody))
	if err != nil {
		return models.TenantData{}, fmt.Errorf("failed to create request: %w", err)
	}
	req.Header.Set("Accept", "application/json")
	req.Header.Set("Content-Type", "application/json")

	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return models.TenantData{}, fmt.Errorf("request failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return models.TenantData{}, fmt.Errorf("unexpected status code: %d", resp.StatusCode)
	}

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return models.TenantData{}, fmt.Errorf("failed to read response body: %w", err)
	}

	var tenantData models.TenantData
	if err := json.Unmarshal(body, &tenantData); err != nil {
		return models.TenantData{}, fmt.Errorf("failed to parse JSON: %w", err)
	}
	return tenantData, nil
}

func FetchCategory(name string, tenantId string) (models.Category, error) {
	secret, err := utils.GetEnvSecrets()
	if err != nil {
		return models.Category{}, err
	}

	// Use the URL from GetEnvSecrets (which now includes fallback)
	baseURL := secret.URL
	if strings.TrimSpace(baseURL) == "" {
		baseURL = "https://api.printdeal.com/api"
	}

	// Ensure URL has protocol
	if !strings.HasPrefix(baseURL, "http://") && !strings.HasPrefix(baseURL, "https://") {
		baseURL = "https://" + baseURL
	}

	url := baseURL + "/products/categories"
	fmt.Printf("DEBUG categories URL: %s\n", url)
	client := &http.Client{Timeout: 30 * time.Second}

	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return models.Category{}, err
	}

	req.Header.Add("API-Secret", secret.APISecret)
	req.Header.Add("User-ID", secret.UserID)
	req.Header.Add("accept", "application/vnd.printdeal-api.v2")

	resp, err := client.Do(req)
	if err != nil {
		return models.Category{}, err
	}
	defer resp.Body.Close()

	fmt.Printf("DEBUG categories status: %d\n", resp.StatusCode)
	// Guard non-200 and empty bodies for clearer diagnostics
	if resp.StatusCode != http.StatusOK {
		dbgBody, _ := io.ReadAll(resp.Body)
		return models.Category{}, fmt.Errorf("categories API error %d: %s (url=%s)", resp.StatusCode, string(dbgBody), url)
	}

	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return models.Category{}, err
	}
	if len(body) == 0 {
		return models.Category{}, fmt.Errorf("categories API returned empty body (url=%s)", url)
	}

	var categories []models.Category
	if err := json.Unmarshal(body, &categories); err != nil {
		return models.Category{}, fmt.Errorf("JSON unmarshal error: %v, body: %s", err, string(body))
	}

	// First try to find by SKU (UUID) - for cases where Python passes UUID
	for _, cat := range categories {
		if cat.Sku == name {
			return cat, nil
		}
	}
	
	// If not found by SKU, try to find by name (original behavior)
	for _, cat := range categories {
		if strings.ToLower(cat.Name) == strings.ToLower(name) {
			return cat, nil
		}
	}
	
	return models.Category{}, fmt.Errorf("category %s not found", name)
}

func extractQuantityAndDeliveryBoxes(items []models.Item) (models.Property, models.Property) {
	quantitySet := make(map[int]bool)
	deliveryDaysSet := make(map[int]bool)

	for _, item := range items {
		for _, price := range item.Product.Prices {
			quantitySet[price.Quantity] = true
			if price.Information.DeliveryDays > 0 {
				deliveryDaysSet[price.Information.DeliveryDays] = true
			}
		}
	}

	quantityOptions := []models.Option{}
	for q := range quantitySet {
		slug := fmt.Sprintf("%d_qty", q)
		name := fmt.Sprintf("%d", q)
		quantityOptions = append(quantityOptions, models.Option{
			Slug:     slug,
			Name:     name,
			Nullable: false,
			Width:    nil,
			Height:   nil,
			Parent:   "quantity",
			Excludes: [][]string{},
		})
	}

	deliveryOptions := []models.Option{}
	for d := range deliveryDaysSet {
		slug := fmt.Sprintf("%d_days", d)
		name := fmt.Sprintf("%d days", d)
		deliveryOptions = append(deliveryOptions, models.Option{
			Slug:     slug,
			Name:     name,
			Nullable: false,
			Width:    nil,
			Height:   nil,
			Parent:   "delivery_days",
			Excludes: [][]string{},
		})
	}

	quantityProperty := models.Property{
		Slug:    "quantity",
		Title:   "Quantity",
		Locked:  false,
		Options: quantityOptions,
	}

	deliveryProperty := models.Property{
		Slug:    "delivery_days",
		Title:   "Delivery Days",
		Locked:  false,
		Options: deliveryOptions,
	}

	return quantityProperty, deliveryProperty
}

func GenerateExcludedMatrix(categoryName string, tenantId string) (models.CategoryResponse, error) {
	category, err := FetchCategory(categoryName, tenantId)

	if err != nil {
		return models.CategoryResponse{}, err
	}
	sku := category.Sku
	items, err := fetchDataFromAPI(sku, tenantId)
	if err != nil {
		return models.CategoryResponse{}, err
	}

	attributeOrder := []string{}
	categoryKey := toSnake(category.Name)
	attributeValues := make(map[string]map[string]bool)
	cooccurrence := make(map[string]map[string]bool)

	for _, item := range items {
		comboValues := []string{}
		for _, attr := range item.Product.Attributes {
			if _, ok := attributeValues[attr.Attribute]; !ok {
				attributeValues[attr.Attribute] = make(map[string]bool)
				attributeOrder = append(attributeOrder, attr.Attribute)
			}
			attributeValues[attr.Attribute][attr.Value] = true
			comboValues = append(comboValues, attr.Value)
		}
		for _, v := range comboValues {
			if _, ok := cooccurrence[v]; !ok {
				cooccurrence[v] = make(map[string]bool)
			}
			for _, o := range comboValues {
				if v != o {
					cooccurrence[v][o] = true
				}
			}
		}
	}

	mutualExcludes := make(map[string]map[string]bool)
	for _, group := range rules.CustomExcludes[categoryKey] {
		for i := 0; i < len(group); i++ {
			for j := 0; j < len(group); j++ {
				if i == j {
					continue
				}
				a := toSnake(group[i])
				b := toSnake(group[j])
				if _, ok := mutualExcludes[a]; !ok {
					mutualExcludes[a] = make(map[string]bool)
				}
				mutualExcludes[a][b] = true
			}
		}
	}

	customMultiExcludesMap := make(map[string][][]string)
	if multiGroups, ok := rules.CustomMultiExcludes[categoryKey]; ok {
		for _, group := range multiGroups {
			snakes := []string{}
			for _, val := range group {
				snakes = append(snakes, toSnake(val))
			}
			for i, a := range snakes {
				others := append([]string{}, snakes[:i]...)
				others = append(others, snakes[i+1:]...)
				customMultiExcludesMap[a] = append(customMultiExcludesMap[a], others)
			}
		}

	}

	properties := []models.Property{}
	for _, attr := range attributeOrder {
		values := attributeValues[attr]
		slug := toSnake(attr)
		options := []models.Option{}
		for val := range values {
			snakeVal := toSnake(val)
			excludeSet := make(map[string]bool)
			for otherAttr, otherValues := range attributeValues {
				if otherAttr == attr {
					continue // skip siblings
				}
				for other := range otherValues {
					if other == val {
						continue
					}
					snakeOther := toSnake(other)
					if inPairList(rules.CustomWhitelist[categoryKey], snakeVal, snakeOther) {
						continue
					}
					if !cooccurrence[val][other] || (mutualExcludes[snakeVal] != nil && mutualExcludes[snakeVal][snakeOther]) {
						excludeSet[snakeOther] = true
					}
				}
			}

			excludes := [][]string{}
			for ex := range excludeSet {
				excludes = append(excludes, []string{ex})
			}

			// Append all custom multi excludes lists
			if multi, ok := customMultiExcludesMap[snakeVal]; ok {
				excludes = append(excludes, multi...)
			}

			options = append(options, models.Option{
				Slug:     snakeVal,
				Name:     val,
				Nullable: false,
				Width:    nil,
				Height:   nil,
				Parent:   slug,
				Excludes: excludes,
			})
		}
		properties = append(properties, models.Property{
			Slug:    slug,
			Title:   attr,
			Locked:  false,
			Options: options,
		})
	}

	quantityBox, deliveryBox := extractQuantityAndDeliveryBoxes(items)
	properties = append(properties, quantityBox, deliveryBox)

	now := time.Now().UTC().Format(time.RFC3339)
	return models.CategoryResponse{
		SKU:              sku,
		Active:           true,
		TitleSingle:      category.Name,
		TitlePlural:      category.Name,
		CreatedAt:        now,
		UpdatedAt:        now,
		IntroductionDate: now,
		Properties:       properties,
	}, nil
}

func toSnake(s string) string {
	s = strings.ReplaceAll(s, "/", " ")
	s = strings.ReplaceAll(s, "-", " ")
	s = strings.ReplaceAll(s, ".", " ")
	s = strings.ToLower(strings.TrimSpace(s))
	s = strings.ReplaceAll(s, " ", "_")
	return s
}

func inPairList(list [][]string, a, b string) bool {
	a = toSnake(a)
	b = toSnake(b)
	for _, pair := range list {
		if len(pair) < 2 {
			continue
		}
		x := toSnake(pair[0])
		y := toSnake(pair[1])
		if (x == a && y == b) || (x == b && y == a) {
			return true
		}
	}
	return false
}

func fetchDataFromAPI(uuid string, tenantId string) ([]models.Item, error) {
	secret, err := utils.GetEnvSecrets()
	if err != nil {
		return nil, err
	}

	url := fmt.Sprintf("%s/products/%s/combinations", secret.URL, uuid)
	client := &http.Client{}

	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil, err
	}

	req.Header.Add("accept", "application/vnd.printdeal-api.v2")
	req.Header.Add("API-Secret", secret.APISecret)
	req.Header.Add("User-ID", secret.UserID)

	resp, err := client.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		bodyText, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("API error %d: %s", resp.StatusCode, string(bodyText))
	}

	body, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, err
	}

	var items []models.Item
	if err := json.Unmarshal(body, &items); err != nil {
		return nil, fmt.Errorf("failed to unmarshal items: %w", err)
	}

	return items, nil
}

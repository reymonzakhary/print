package models

type Category struct {
	Name                   string `json:"name"`
	Sku                    string `json:"sku"`
	CombinationsModifiedAt string `json:"combinationsModifiedAt"`
}

type Attribute struct {
	Attribute string `json:"attribute"`
	Value     string `json:"value"`
}

type Product struct {
	Attributes []Attribute `json:"attributes"`
	Prices     []Price     `json:"prices"`
}

type Item struct {
	Product Product `json:"product"`
}

type Option struct {
	Slug     string     `json:"slug"`
	Name     string     `json:"name"`
	Nullable bool       `json:"nullable"`
	Width    *int       `json:"width"`
	Height   *int       `json:"height"`
	Parent   string     `json:"parent"`
	Excludes [][]string `json:"excludes"`
}

type Property struct {
	Slug    string   `json:"slug"`
	Title   string   `json:"title"`
	Locked  bool     `json:"locked"`
	Options []Option `json:"options"`
}

type CategoryResponse struct {
	SKU              string     `json:"sku"`
	Active           bool       `json:"active"`
	TitleSingle      string     `json:"titleSingle"`
	TitlePlural      string     `json:"titlePlural"`
	CreatedAt        string     `json:"createdAt"`
	UpdatedAt        string     `json:"updatedAt"`
	IntroductionDate string     `json:"introductionDate"`
	Properties       []Property `json:"properties"`
}

type CustomRules struct {
	CustomWhitelist     map[string][][]string `json:"custom_whitelist"`
	CustomExcludes      map[string][][]string `json:"custom_excludes"`
	CustomMultiExcludes map[string][][]string `json:"custom_multi_excludes"`
}

type Price struct {
	Quantity    int     `json:"quantity"`
	Price       float64 `json:"price"`
	Information struct {
		DeliveryDays int `json:"deliveryDays"`
	} `json:"information"`
}

type Information struct {
	DeliveryDays int `json:"deliveryDays"`
}

type TenantData struct {
	Error string     `json:"error"`
	Data  TenantInfo `json:"data"`
}

type TenantInfo struct {
	URL       string `json:"dwd-url"`
	APISecret string `json:"dwd-secret"`
	UserID    string `json:"dwd-user-id"`
}

type SyncRequest struct {
	TenantID   string   `json:"tenant_id"`
	Vendor     string   `json:"vendor"`
	TenantName string   `json:"tenant_name"`
	SKUs       []string `json:"skus"`
}

type SecretData struct {
	APISecret string `json:"api_secret"`
	UserID    string `json:"user_id"`
	URL       string `json:"url"`
}

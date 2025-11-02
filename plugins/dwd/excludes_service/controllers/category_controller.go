package controllers

import (
	"dwd/models"
	"dwd/services"
	"github.com/gin-gonic/gin"
	"net/http"
	"strings"
)

func FetchCategory(c *gin.Context) {
	name := c.Query("name")
	tenantID := c.Query("tenant_id")

	if name == "" || tenantID == "" {
		c.JSON(http.StatusBadRequest, gin.H{
			"data":    []interface{}{},
			"message": "name and tenant_id are required",
			"status":  http.StatusBadRequest,
		})
		return
	}

	category, err := services.FetchCategory(name, tenantID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"data":    []interface{}{},
			"message": err.Error(),
			"status":  http.StatusInternalServerError,
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": []interface{}{category}})
}

func FetchExcludedMatrix(c *gin.Context) {
	var request models.SyncRequest
	if err := c.ShouldBindJSON(&request); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{
			"data":    []interface{}{},
			"message": err.Error(),
			"status":  http.StatusBadRequest,
		})
		return
	}

	if len(request.SKUs) == 0 {
		c.JSON(http.StatusBadRequest, gin.H{
			"data":    []interface{}{},
			"message": "No SKUs provided",
			"status":  http.StatusBadRequest,
		})
		return
	}

	category := strings.ToLower(request.SKUs[0]) // only handling the first SKU
	tenantID := request.TenantID

	response, err := services.GenerateExcludedMatrix(category, tenantID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"data":    []interface{}{},
			"message": err.Error(),
			"details": err.Error(),
			"status":  http.StatusInternalServerError,
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": []interface{}{response}})
}

func FetchSecrets(c *gin.Context) {
	var requestBody struct {
		TenantID string `json:"tenant_id"`
	}

	if err := c.ShouldBindJSON(&requestBody); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{
			"data":    []interface{}{},
			"message": err.Error(),
			"status":  http.StatusBadRequest,
		})
		return
	}

	response, err := services.GetSecrets(requestBody.TenantID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{
			"data":    []interface{}{},
			"message": err.Error(),
			"status":  http.StatusInternalServerError,
		})
		return
	}

	c.JSON(http.StatusOK, gin.H{"data": response})
}

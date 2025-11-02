package main

import (
	"dwd/routes"
	"dwd/services"
	"github.com/gin-gonic/gin"
	"log"
)

func init() {
}

func main() {
	log.Println("Starting DWD API service")
	if err := services.LoadCustomRules("rules/custom_rules.json"); err != nil {
		log.Fatalf("Failed to load rules: %v", err)
	}

	r := gin.Default()
	routes.RegisterRoutes(r)

	r.Run(":5000")
}

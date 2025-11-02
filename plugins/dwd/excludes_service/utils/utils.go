package utils

import (
	"dwd/models"
	"fmt"
	"github.com/joho/godotenv"
	"os"
)

func GetEnvSecrets() (models.SecretData, error) {
	// Try to load .env file, but don't fail if it doesn't exist
	if err := godotenv.Load(); err != nil {
		// .env file not found or can't be read - that's okay
		fmt.Printf("INFO: .env file not found, using system environment variables\n")
	}

	secret := os.Getenv("API_SECRET")
	userID := os.Getenv("USER_ID")
	URL := os.Getenv("URL")

	// If env vars are missing, return static fallback data
	if secret == "" || userID == "" || URL == "" {
		fmt.Printf("WARNING: Missing env vars, using static fallback data\n")
		return models.SecretData{
			APISecret: "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
			UserID:    "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
			URL:       "https://api.printdeal.com/api",
		}, nil
	}

	return models.SecretData{
		APISecret: secret,
		UserID:    userID,
		URL:       URL,
	}, nil
}

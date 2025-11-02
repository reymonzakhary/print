package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"os"
	"sync"
	"time"

	"github.com/gin-gonic/gin"
)

var (
	jobMutex sync.Mutex
)

func generateCombinations(lists [][]interface{}) [][]interface{} {
	// Validate if lists are empty
	if len(lists) == 0 {
		fmt.Println("Error: Empty input lists")
		return nil
	}

	// Calculate the number of combinations
	n := 1
	for _, list := range lists {
		if len(list) == 0 {
			fmt.Println("Error: One of the lists is empty")
			return nil
		}
		n *= len(list)
	}

	// Prepare the result slice
	result := make([][]interface{}, n)

	// Loop to generate combinations
	for i := 0; i < n; i++ {
		combination := make([]interface{}, len(lists))
		for j, list := range lists {
			combination[j] = list[(i/len(lists[j]))%len(list)]
		}
		result[i] = combination
	}

	return result
}

// Append data to the result file
func appendToFile(filename string, data []byte) error {
	// Open the file in append mode
	file, err := os.OpenFile(filename, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0644)
	if err != nil {
		return err
	}
	defer file.Close()

	// Write the data
	_, err = file.Write(append(data, '\n')) // Append with newline
	if err != nil {
		return err
	}

	return nil
}

// Process each key's data and append to the result file
func processKey(key string, value [][]interface{}, resultFile string, wg *sync.WaitGroup) {
	defer wg.Done() // Decrement the counter when the goroutine completes

	// Generate combinations for this key
	combinations := generateCombinations(value)

	// Lock the result file for writing
	jobMutex.Lock()
	defer jobMutex.Unlock()

	// Prepare the result data
	result := map[string][][]interface{}{
		key: combinations,
	}

	// Marshal result to JSON format
	resultContent, err := json.Marshal(result)
	if err != nil {
		fmt.Printf("Error marshalling result for key '%s': %v\n", key, err)
		return
	}

	// Append result to the result file
	err = appendToFile(resultFile, resultContent)
	if err != nil {
		fmt.Printf("Error appending result for key '%s': %v\n", key, err)
	}
}

// Process all keys from the file and append the results to a single file
func processCombinationsForAllKeysFromFile(resultFile string, chunkSize int) {
	// Open the input JSON file (data.json)
	fileContent, err := ioutil.ReadFile("data.json")
	if err != nil {
		fmt.Printf("Error reading file: %v\n", err)
		return
	}

	// Unmarshal the data into a map
	var data map[string][][]interface{}
	if err := json.Unmarshal(fileContent, &data); err != nil {
		fmt.Printf("Error unmarshalling data: %v\n", err)
		return
	}

	// Create a worker pool to process the keys
	var wg sync.WaitGroup

	// Iterate through the data in chunks
	keys := make([]string, 0, len(data))
	for key := range data {
		keys = append(keys, key)
	}

	for i := 0; i < len(keys); i += chunkSize {
		end := i + chunkSize
		if end > len(keys) {
			end = len(keys)
		}

		// Process each chunk in the worker pool
		for _, key := range keys[i:end] {
			value := data[key]
			wg.Add(1) // Increment the counter for each goroutine
			go processKey(key, value, resultFile, &wg)
		}

		// Wait for all goroutines to complete before processing the next chunk
		wg.Wait()
	}
}

// Generate a unique job ID
func generateJobID() string {
	return fmt.Sprintf("%d", time.Now().UnixNano())
}

func main() {
	// Initialize Gin router
	r := gin.Default()

	// Endpoint to generate combinations for all keys and run the background task
	r.POST("/generate-combinations", func(c *gin.Context) {
		// Define the result file name
		resultFile := "result.json"

		// Start the background task to process all keys from the file in chunks
		go processCombinationsForAllKeysFromFile(resultFile, 10) // Process in chunks of 10 keys

		// Return a success message to the client
		c.JSON(200, gin.H{"message": "Processing started"})
	})

	// Endpoint to retrieve the result of the background task
	r.GET("/result", func(c *gin.Context) {
		// Define the result file name
		resultFile := "result.json"

		// Read the result file
		fileContent, err := ioutil.ReadFile(resultFile)
		if err != nil {
			c.JSON(404, gin.H{"error": "Job not found or still processing"})
			return
		}

		// Return the result to the client
		c.JSON(200, gin.H{"result": string(fileContent)})
	})

	// Start the Gin server on port 8099
	r.Run(":8099")
}

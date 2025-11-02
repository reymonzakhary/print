package routes

import (
	"dwd/controllers"
	"github.com/gin-gonic/gin"
)

func RegisterRoutes(r *gin.Engine) {
	r.GET("/category", controllers.FetchCategory)
	r.POST("/sync", controllers.FetchExcludedMatrix)
	r.GET("/secrets", controllers.FetchSecrets)
}

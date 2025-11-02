FROM golang:1.24.3

WORKDIR /var/www

COPY . .

# This will clean up the go.mod and ensure dependencies are properly resolved
RUN go mod tidy


# Now download dependencies
RUN go mod download

RUN go build -o main .

EXPOSE 5000

CMD ["./main"]
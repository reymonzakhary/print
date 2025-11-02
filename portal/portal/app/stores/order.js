export const useOrderStore = defineStore("order", {
  state: () => ({
    // Quotes data - map of quote IDs to quote objects
    quotes: {},

    // Orders data - map of order IDs to order objects
    orders: {},

    // Active quote/order being edited
    activeOrderId: null,
    activeOrderType: null, // 'quote' or 'order'

    // Loading and error states
    isLoading: false,
    error: null,

    // Demo data (would be replaced with API calls in real implementation)
    demoQuotes: {
      1001: {
        id: "1001",
        name: "Flyers zomer campagne",
        createdAt: "2025-03-01T12:00:00",
        status: "draft",
        recipient: {
          name: "Marketing Afdeling",
          email: "marketing@example.com",
        },
        items: [],
        notes: "",
      },
      1002: {
        id: "1002",
        name: "Visitekaartjes medewerkers",
        createdAt: "2025-02-15T09:30:00",
        status: "sent",
        recipient: {
          name: "HR Afdeling",
          email: "hr@example.com",
        },
        items: [],
        notes: "Spoedbestelling voor nieuwe medewerkers",
      },
      1003: {
        id: "1003",
        name: "Print materiaal event",
        createdAt: "2025-03-05T15:45:00",
        status: "draft",
        recipient: {
          name: "Event Team",
          email: "events@example.com",
        },
        items: [],
        notes: "Benodigd voor het evenement op 15 april",
      },
    },
  }),

  getters: {
    // Get all quotes
    getAllQuotes: (state) => Object.values(state.quotes),

    // Get all orders
    getAllOrders: (state) => Object.values(state.orders),

    // Get active order data
    getActiveOrder: (state) => {
      if (!state.activeOrderId || !state.activeOrderType) return null;

      return state.activeOrderType === "quote"
        ? state.quotes[state.activeOrderId]
        : state.orders[state.activeOrderId];
    },

    // Get quote by ID
    getQuoteById: (state) => (id) => state.quotes[id] || null,

    // Get order by ID
    getOrderById: (state) => (id) => state.orders[id] || null,

    // Get recent quotes (last 5)
    getRecentQuotes: (state) => {
      return Object.values(state.quotes)
        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
        .slice(0, 5);
    },

    // Get recent orders (last 5)
    getRecentOrders: (state) => {
      return Object.values(state.orders)
        .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
        .slice(0, 5);
    },

    // Get loading state
    isOrderDataLoading: (state) => state.isLoading,

    // Get error state
    hasOrderError: (state) => state.error !== null,

    // Get error message
    orderErrorMessage: (state) => state.error,
  },

  actions: {
    // Initialize the store with demo data (would be replaced with actual API calls)
    initializeStore() {
      this.quotes = { ...this.demoQuotes };
      this.orders = {};
    },

    // Fetch all quotes
    async fetchQuotes() {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        // For demo purposes, we're just using the demo data
        this.quotes = { ...this.demoQuotes };

        return Object.values(this.quotes);
      } catch (error) {
        this.error = error.message || "Failed to fetch quotes";
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Fetch all orders
    async fetchOrders() {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        // For demo purposes, we're just keeping the empty object

        return Object.values(this.orders);
      } catch (error) {
        this.error = error.message || "Failed to fetch orders";
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Fetch a single quote by ID
    async fetchQuoteById(id) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 300));

        // For demo purposes, we're just using the demo data
        const quote = this.demoQuotes[id];

        if (!quote) {
          throw new Error(`Quote with ID ${id} not found`);
        }

        this.quotes[id] = quote;

        return quote;
      } catch (error) {
        this.error = error.message || `Failed to fetch quote: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Fetch a single order by ID
    async fetchOrderById(id) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 300));

        if (!this.orders[id]) {
          throw new Error(`Order with ID ${id} not found`);
        }

        return this.orders[id];
      } catch (error) {
        this.error = error.message || `Failed to fetch order: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Set active order
    setActiveOrder(id, type) {
      this.activeOrderId = id;
      this.activeOrderType = type;
    },

    // Clear active order
    clearActiveOrder() {
      this.activeOrderId = null;
      this.activeOrderType = null;
    },

    // Create a new quote
    async createQuote(quoteData) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 700));

        const newId = `quote-${Date.now()}`;

        const newQuote = {
          id: newId,
          name: quoteData.name || `Quote ${newId}`,
          createdAt: new Date().toISOString(),
          status: "draft",
          recipient: quoteData.recipient || { name: "", email: "" },
          items: [],
          notes: quoteData.notes || "",
        };

        this.quotes[newId] = newQuote;

        return newQuote;
      } catch (error) {
        this.error = error.message || "Failed to create quote";
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Create a new order
    async createOrder(orderData) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 700));

        const newId = `order-${Date.now()}`;

        const newOrder = {
          id: newId,
          type: orderData.type || "order",
          quoteId: orderData.quoteId || null,
          productVariantId: orderData.productVariantId,
          quantity: orderData.quantity,
          price: orderData.price,
          producerId: orderData.producerId,
          createdAt: new Date().toISOString(),
          status: "new",
          recipient: orderData.recipient || { name: "", email: "" },
          notes: orderData.notes || "",
          options: orderData.options || {},
        };

        // If this order is for an existing quote, add the item to the quote
        if (orderData.type === "quote" && orderData.quoteId) {
          const quote = this.quotes[orderData.quoteId];

          if (quote) {
            quote.items.push({
              id: `item-${Date.now()}`,
              productVariantId: orderData.productVariantId,
              quantity: orderData.quantity,
              price: orderData.price,
              producerId: orderData.producerId,
              options: orderData.options || {},
            });
          }

          return { id: orderData.quoteId, type: "quote" };
        }

        // Otherwise, create a new order
        this.orders[newId] = newOrder;

        return { id: newId, type: "order" };
      } catch (error) {
        this.error = error.message || "Failed to create order";
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Update a quote
    async updateQuote(id, quoteData) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        if (!this.quotes[id]) {
          throw new Error(`Quote with ID ${id} not found`);
        }

        // Update only the provided fields
        this.quotes[id] = {
          ...this.quotes[id],
          ...quoteData,
          // Prevent overwriting these fields unless explicitly provided
          id: id,
          createdAt: quoteData.createdAt || this.quotes[id].createdAt,
        };

        return this.quotes[id];
      } catch (error) {
        this.error = error.message || `Failed to update quote: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Update an order
    async updateOrder(id, orderData) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        if (!this.orders[id]) {
          throw new Error(`Order with ID ${id} not found`);
        }

        // Update only the provided fields
        this.orders[id] = {
          ...this.orders[id],
          ...orderData,
          // Prevent overwriting these fields unless explicitly provided
          id: id,
          createdAt: orderData.createdAt || this.orders[id].createdAt,
        };

        return this.orders[id];
      } catch (error) {
        this.error = error.message || `Failed to update order: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Delete a quote
    async deleteQuote(id) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        if (!this.quotes[id]) {
          throw new Error(`Quote with ID ${id} not found`);
        }

        delete this.quotes[id];

        // If this was the active quote, clear the active order
        if (this.activeOrderId === id && this.activeOrderType === "quote") {
          this.clearActiveOrder();
        }

        return { success: true };
      } catch (error) {
        this.error = error.message || `Failed to delete quote: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Delete an order
    async deleteOrder(id) {
      try {
        this.isLoading = true;
        this.error = null;

        // In a real implementation, this would be an API call
        await new Promise((resolve) => setTimeout(resolve, 500));

        if (!this.orders[id]) {
          throw new Error(`Order with ID ${id} not found`);
        }

        delete this.orders[id];

        // If this was the active order, clear the active order
        if (this.activeOrderId === id && this.activeOrderType === "order") {
          this.clearActiveOrder();
        }

        return { success: true };
      } catch (error) {
        this.error = error.message || `Failed to delete order: ${id}`;
        throw error;
      } finally {
        this.isLoading = false;
      }
    },

    // Clear error state
    clearOrderError() {
      this.error = null;
    },
  },
});

import { useStore } from "vuex";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

export default defineNuxtPlugin({
  name: "echo",
  dependsOn: ["init"],
  setup(nuxtApp) {
    const authStore = useAuthStore();
    if (!authStore.check()) return;

    const api = useAPI();
    const store = useStore();

    if (import.meta.client) {
      window.Pusher = Pusher;
    }

    const echo = new Echo({
      broadcaster: "pusher",
      authEndpoint:
        window.location.protocol +
        "//" +
        window.location.hostname +
        "/api/v1/mgr/broadcasting/auth",
      authorizer: (channel) => {
        return {
          authorize: (socketId, callback) => {
            api
              .post("/broadcasting/auth", { socket_id: socketId, channel_name: channel.name })
              .then((response) => {
                callback(false, response);
              })
              .catch((error) => {
                callback(true, error);
              });
          },
        };
      },
      key: window.location.hostname,
      wsHost: window.location.hostname,
      wsPort: 6001,
      forceTLS: false,
      disableStats: true,
      enabledTransports: ["wss", "ws"],
      cluster: "mt1",
    });

    if (import.meta.client) {
      window.addEventListener("beforeunload", () => {
        echo.leaveAllChannels();
      });
    }

    nuxtApp.provide("echo", echo);
    nuxtApp.hook("app:mounted", async () => {
      initializeWebSockets(authStore.permissions);
    });
    function initializeWebSockets(permissions) {
      const router = useRouter();
      const route = useRoute();

      const { theUser: me } = storeToRefs(useAuthStore());

      /*******************************
       ****** ExternalMessage *******
       ******************************/
      echo.private("messages").listen("Messages.CrossTenantMessage", (e) => {
        store.commit("notification/set_notification", {
          title: `${e.message.sender_name}: ${e.message.title}`,
          text: `${e.message.body}`,
          link: `/messages`,
          status: "new",
          color: "blue",
        });
        store.commit("notification/set_status", true);
      });

      if (permissions.includes("print-assortments-categories-list")) {
        echo.private("products").listen("Tenant.Products.FinishedProductCombinationEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Assortment",
            text: `Successfully generated ${e.response.data} products from ${e.category}`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });

        echo.private("products").listen("Tenant.Categories.FinishedPriceGenerateEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Assortment",
            text: e.message.message,
            status: "new",
            color: e.message.status === 200 ? "green" : "red",
          });
          store.commit("notification/set_status", true);
        });

        // export category to file
        // finish
        echo.private("products").listen("Tenant.Categories.FinishedCategoryProductsExport", (e) => {
          store.commit("notification/set_notification", {
            title: "Assortment",
            text: `Successfully exported ${e.category} to ${e.path}`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });

        // import category to file
        // finish
        echo.private("products").listen("Tenant.Categories.FinishedCategoryProductsImport", (e) => {
          store.commit("notification/set_notification", {
            title: "Assortment",
            text: `Successfully imported ${e.category} to ${e.path}`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });
      }

      /*******************************
       ********** ORDERS ************
       ******************************/
      if (permissions.includes("orders-list")) {
        echo.private("orders").listen("Produce.ChangeSystemItemStatusEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `Item ${e.item.id}, status has been Updated!`,
            link: `/orders/${e.item.id}`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });

        echo
          .private(`customerOrders.${me.value.id}`)
          .listen("Tenant.Order.CreateOrderForCustomerEvent", (e) => {
            store.commit("notification/set_notification", {
              title: "Orders",
              text: `new order has been created.#${e.order.id}`,
              link: `/orders/${e.order.id}`,
              status: "new",
              color: "green",
            });
            store.commit("notification/set_status", true);
          });

        echo
          .private(`customerOrders.${me.value.id}`)
          .listen("Tenant.Order.UpdateOrderForCustomerEvent", (e) => {
            store.commit("notification/set_notification", {
              title: "Orders",
              text: `Your Order .#${e.order.id} Has Been Updated!`,
              link: `/orders/${e.order.id}`,
              status: "new",
              color: "green",
            });
            if (route.name === "orders-id") {
              // Force reload by temporary route trick
              if (import.meta.client) {
                window.dispatchEvent(
                  new CustomEvent("refresh-orders", {
                    detail: { orderId: e.order_id, itemId: e.item_id },
                  }),
                );
              }
            } else if (route.name === "orders") {
              if (import.meta.client) {
                window.dispatchEvent(new CustomEvent("refresh-all-orders"));
              }
            }
            store.commit("notification/set_status", true);
          });

        echo
          .private(`customerOrders.${me.value.id}`)
          .listen("Tenant.Order.ArchiveOrderForCustomerEvent", (e) => {
            store.commit("notification/set_notification", {
              title: "Orders",
              text: `Your Order .#${e.order.id} Has Been Archived.`,
              link: `/orders/${e.order.id}`,
              status: "new",
              color: "orange",
            });
            store.commit("notification/set_status", true);
          });

        echo.private("orders").listen("Tenant.Order.CreateOrderEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `new order has been created.#${e.order.id}`,
            link: `/orders/${e.order.id}`,
            status: "new",
            color: "green",
          });
          if (route.name === "orders-id") {
            // Force reload by temporary route trick
            if (import.meta.client) {
              window.dispatchEvent(
                new CustomEvent("refresh-orders", {
                  detail: { orderId: e.order_id, itemId: e.item_id },
                }),
              );
            }
          } else if (route.name === "orders") {
            if (import.meta.client) {
              window.dispatchEvent(new CustomEvent("refresh-all-orders"));
            }
          }
          store.commit("notification/set_status", true);
        });

        echo.private("orders").listen("Tenant.Order.UpdateOrderEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `order #${e.order.id} has been updated.`,
            link: `/orders/${e.order.id}`,
            status: "new",
            color: "green",
          });
          if (route.name === "orders-id") {
            // Force reload by temporary route trick
            if (import.meta.client) {
              window.dispatchEvent(
                new CustomEvent("refresh-orders", {
                  detail: { orderId: e.order_id, itemId: e.item_id },
                }),
              );
            }
          } else if (route.name === "orders") {
            if (import.meta.client) {
              window.dispatchEvent(new CustomEvent("refresh-all-orders"));
            }
          }
          store.commit("notification/set_status", true);
        });

        echo.private("orders").listen("Tenant.Order.Item.ProduceItemEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `Your item #${e.item.id} has been produced! to supplier as order #${e.producerOrderId}`,
            link: `/orders/${e.order.id}`,
            status: "in-progress",
            color: "green",
          });

          if (route.name === "orders-id") {
            // Force reload by temporary route trick
            if (import.meta.client) {
              window.dispatchEvent(
                new CustomEvent("refresh-orders", {
                  detail: { orderId: e.order_id, itemId: e.item_id },
                }),
              );
            }
          } else if (route.name === "orders") {
            if (import.meta.client) {
              window.dispatchEvent(new CustomEvent("refresh-all-orders"));
            }
          }
          store.commit("notification/set_status", true);
        });

        echo.private("orders").listen("Tenant.Order.Item.FailedProduceItemEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `Your item #${e.item.id} has failed to produce.`,
            link: `/orders/${e.order.id}`,
            status: "new",
            color: "red",
          });

          if (route.name === "orders-id") {
            // Force reload by temporary route trick
            if (import.meta.client) {
              window.dispatchEvent(
                new CustomEvent("refresh-orders", {
                  detail: { orderId: e.order_id, itemId: e.item_id },
                }),
              );
            }
          } else if (route.name === "orders") {
            if (import.meta.client) {
              window.dispatchEvent(new CustomEvent("refresh-all-orders"));
            }
          }
          store.commit("notification/set_status", true);
        });

        echo.private("orders").listen("Tenant.Order.Item.ChangeItemStatusEvent", async (e) => {
          store.commit("notification/set_notification", {
            title: "Orders",
            text: `Your item #${e.item_id} Status Has been changed! to supplier at order #${e.order_id}`,
            link: `/orders/${e.order_id}`,
            status: "in-progress",
            color: "green",
          });

          if (route.name === "orders-id") {
            // Force reload by temporary route trick
            if (import.meta.client) {
              window.dispatchEvent(
                new CustomEvent("refresh-orders", {
                  detail: { orderId: e.order_id, itemId: e.item_id },
                }),
              );
            }
          } else if (route.name === "orders") {
            if (import.meta.client) {
              window.dispatchEvent(new CustomEvent("refresh-all-orders"));
            }
          }
          store.commit("notification/set_status", true);
        });
      }

      /*******************************
       ********** QUOTATIONS ************
       ******************************/
      if (permissions.includes("quotations-list")) {
        echo.private("quotations").listen("Tenant.Order.CreateQuotationEvent", (e) => {
          store.commit("notification/set_notification", {
            title: "Quotations",
            text: `new quotation has been created. ${e.order.id}`,
            link: `/orders/${e.order.id}`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });
      }

      /*******************************
       ********** FILEMANAGER *******
       ******************************/
      if (permissions.includes("auth-access")) {
        echo.private("fm").listen("Tenant.FM.UnzippedEvent", () => {
          store.commit("notification/set_notification", {
            title: "Media Manager",
            text: `Your zip-file is successfully unzipped.`,
            status: "new",
            color: "green",
          });
          store.fm.content.dispatch("refreshManagers");
          store.commit("notification/set_status", true);
        });
        echo.private("fm").listen("Tenant.FM.ZippedEvent", () => {
          store.commit("notification/set_notification", {
            title: "Media Manager",
            text: `Your file is successfully zipped.`,
            status: "new",
            color: "green",
          });
          store.commit("notification/set_status", true);
        });

        /*******************************
         ******* DESIGN TEMPLATES ******
         *******************************/
        if (permissions.includes("design-providers-templates-list")) {
          echo
            .private("designProviderTemplate")
            .listen("Tenant.FM.FinishedExtractingDesignProviderTemplate", (e) => {
              store.commit("notification/set_notification", {
                title: "Design Tools",
                text: `Your template ${e.designProviderTemplate.name} with id ${e.designProviderTemplate.id} is extracted.`,
                status: "new",
                color: "green",
              });
              this.$store.state.fm.content.dispatch("refreshManagers");
              store.commit("notification/set_status", true);
            });
        }

        /*******************************
         ********** CAMPAIGNS **********
         *******************************/
        if (permissions.includes("campaigns-list")) {
          echo.private("campaigns").listen("FinishedExportCampaignEvent", (e) => {
            store.commit("campaigns/set_selected_campaign_exports", e.export);
            store.commit("campaigns/set_campaign_generating", false);
            store.commit("notification/set_notification", {
              title: "Campaigns",
              text: e.response.message,
              status: "new",
              color: e.response.status === 200 ? "green" : "orange",
            });
            store.commit("notification/set_status", true);
          });
        }

        /*******************************
         ********** BLUEPRINTS *********
         *******************************/
        if (permissions.includes("auth-access")) {
          // cart worker start
          echo.private("blueprints").listen("Tenant.Blueprints.StartedBlueprintWorkerEvent", () => {
            store.dispatch("cart/get_cart");
          });

          // Blueprint progress
          echo
            .private("blueprints")
            .listen("Tenant.Blueprints.NotificationBlueprintProgressEvent", (e) => {
              store.commit("cart/set_progress", {
                active: e.input.id,
                total: e.total,
                current: e.current,
                action: e.model,
                signature: e.signature,
                input: e.input,
                product: e.productName,
              });
            });

          // Blueprint action failed event
          echo.private("blueprints").listen("Tenant.Blueprints.FailedBlueprintRunnerEvent", (e) => {
            // remove the progress feedback
            store.commit("cart/remove_progress", e.destination.id);

            //  set error notification
            store.commit("notification/set_notification", {
              title: "Blueprint runner",
              text: `Rendering failed on action '${e.action}' in step ${e.step} with the following error: '${e.error}'`,
              status: "new",
              color: "red",
            });

            // update cart to display failed status
            store.dispatch("cart/get_cart");
          });

          // bp worker finish
          echo
            .private("blueprints")
            .listen("Tenant.Blueprints.FinishedBlueprintWorkerEvent", (e) => {
              if (e.input.cart_id) {
                store.dispatch("cart/get_cart");
              }

              if (e.input.order) {
                // const item = e.input.product;
                // const id = e.input.id;

                store.dispatch("orders/get_orders");
                store.dispatch("orders/refreshOrder", e.input.order[0].id);
              }

              store.commit("cart/remove_progress", e.input.id);

              if (e.input.id) {
                store.commit("notification/set_notification", {
                  title: "Blueprint runner",
                  text: `rendering for product with id ${e.input.id} is done`,
                  status: "new",
                  color: "blue",
                });
                store.commit("notification/set_status", true);
              }
            });
        }
      }
    }
  },
});

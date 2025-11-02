<template>
  <main class="mx-auto flex flex-col gap-4 md:grid md:grid-cols-4">
    <transition name="fade">
      <HandshakeReplyModal
        v-if="showHandshakeModal && replyHandshake !== null && replyStatus !== null"
        :handshake="replyHandshake"
        :status="replyStatus"
        :is-loading="sendingMessage"
        @on-send="handleSendReply($event)"
        @on-close="showHandshakeModal = false"
      />
    </transition>
    <header class="flex justify-between mb-2 w-full col-span-12">
      <UICardHeaderTitle title="Contracts" :icon="['fal', 'envelopes-bulk']" />
    </header>

    <section class="space-y-2 rounded border border-blue-400 bg-gray-100 p-4 dark:bg-gray-900">
      <h2
        class="sticky -top-4 mb-2 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
      >
        <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="mr-2" />
        Pending producer requests
      </h2>

      <ContractMessage
        v-for="handshake in pendingHandshakes"
        :key="handshake.sender_name"
        class=""
        :handshake="handshake"
        :me="me"
        @reply="handleHandshakeReply"
      />
    </section>

    <section class="rounded border border-green-400 bg-gray-100 p-4 dark:bg-gray-900">
      <h2
        class="sticky -top-4 mb-2 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
      >
        <font-awesome-icon :icon="['fal', 'handshake']" class="mr-2" />
        Accepted producer requests
      </h2>

      <ContractMessage
        v-for="handshake in acceptedHandshakes"
        :key="handshake.sender_name"
        class=""
        :handshake="handshake"
        :me="me"
        @reply="handleHandshakeReply"
        @config-contract="handleConfigContract"
      />
    </section>

    <section class="rounded border border-amber-400 bg-gray-100 p-4 dark:bg-gray-900">
      <h2
        class="sticky -top-4 mb-2 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
      >
        <font-awesome-icon :icon="['fal', 'handshake-slash']" class="mr-2" />
        Suspended producer requests
      </h2>

      <ContractMessage
        v-for="handshake in suspendedHandshakes"
        :key="handshake.sender_name"
        class=""
        :handshake="handshake"
        :me="me"
        @reply="handleHandshakeReply"
      />
    </section>

    <section class="rounded border border-red-400 bg-gray-100 p-4 dark:bg-gray-900">
      <h2
        class="sticky -top-4 mb-2 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
      >
        <font-awesome-icon :icon="['fal', 'handshake-slash']" class="mr-2" />
        Declined producer requests
      </h2>

      <ContractMessage
        v-for="handshake in declinedHandshakes"
        :key="handshake.sender_name"
        class=""
        :handshake="handshake"
        :me="me"
        @reply="handleHandshakeReply"
      />
    </section>

    <Teleport to="body">
      <UIModalSlideIn
        v-if="showContractConfiguration && contract !== null"
        width="w-11/12 lg:w-2/3"
        :full-height="true"
        class="relative h-full"
        @close="((showContractConfiguration = false), (contract = null))"
      >
        <template #side-panel-header>
          <header class="sticky top-0 bg-white p-4 dark:bg-gray-700">
            <h2 class="text-lg font-bold uppercase tracking-wide">
              Handshake contract configuration - {{ contractDetails.sender }}
            </h2>
            <p class="mt-1 text-sm italic text-gray-500">
              You can apply a discount to your reseller and pick what categories you want to share,
              possibly with a nice specific applied discount.
            </p>
          </header>
        </template>
        <template #side-panel-content>
          <section class="rounded bg-gray-100 p-4 dark:bg-gray-800">
            <h3 class="font-bold uppercase tracking-wide">General</h3>
            <h4 class="my-2 text-sm">
              Discounts for <strong>{{ contractDetails.sender }}</strong>
            </h4>

            <!-- <section class="flex flex-wrap items-center space-x-2 space-y-2">
              <template v-if="contract.custom_fields?.discount?.general?.slots.length > 0">
                <nav class="flex w-full">
                  <button
                    class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                    :class="{
                      'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                        contract.custom_fields?.discount?.general.mode === 'run',
                    }"
                    @click="contract.custom_fields.discount.general.mode = 'run'"
                  >
                    Runs
                  </button>
                  <button
                    class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                    :class="{
                      'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                        contract.custom_fields?.discount?.general.mode === 'price',
                    }"
                    @click="contract.custom_fields.discount.general.mode = 'price'"
                  >
                    Price
                  </button>
                </nav>
                <DiscountTile
                  v-for="(discount, index) in contract.custom_fields?.discount.general.slots"
                  :key="discount.id"
                  :discount="discount"
                  :is-deletable="
                    index === contract.custom_fields?.discount?.general.slots.length - 1
                  "
                  :is-infinitable="
                    index === contract.custom_fields?.discount?.general.slots.length - 1
                  "
                  :is-from-editable="index === 0"
                  @update:discount="handleGeneralDiscountUpdate($event, index)"
                  @on-remove-discount="handleGeneralDiscountDelete(index)"
                />
              </template>
              <DiscountSlot key="DiscountSlot" @add-discount="handleGeneralDiscountAdd" /> -->
            <!-- </section> -->

            <section v-if="loading" class="mt-4">
              <!-- <UIListSkeleton
                :key="'skeleton1'"
                class="w-full"
                :skeleton-line-height="2"
                :skeleton-line-amount="2"
                :skeleton-tone="200"
              />
              <UIListSkeleton
                :key="'skeleton2'"
                class="w-1/4"
                :skeleton-line-height="20"
                :skeleton-line-amount="1"
                :skeleton-tone="200"
              /> -->
            </section>

            <section v-else class="flex flex-col">
              <h3 class="mt-8 font-bold uppercase tracking-wide">your shared categories</h3>
              <section class="relative grid grid-cols-3 gap-4 sm:grid-cols-4">
                <div
                  v-for="(categoryDiscount, i) in contract.custom_fields?.discount?.categories"
                  :key="categoryDiscount.id"
                  class="cursor-pointer select-none rounded bg-white p-4 shadow-md hover:shadow-lg dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
                  :class="{
                    'col-span-full col-start-1': showCategoryDiscountId === categoryDiscount.id,
                  }"
                  @click="showCategoryDiscountId = categoryDiscount.id"
                >
                  <h4
                    v-if="sharedCategories.find((c) => c.id === categoryDiscount.id)"
                    class="mb-2 flex items-center justify-between text-sm font-bold uppercase tracking-wide"
                  >
                    {{
                      $display_name(
                        sharedCategories.find((c) => c.id === categoryDiscount.id)?.display_name,
                      )
                    }}

                    {{ contract.value?.custom_fields?.categories }}
                    <!-- {{ $display_name(sharedCategories.display_name) }} -->
                    <UIButton
                      class="ml-2"
                      :variant="
                        contract.custom_fields?.categories?.find(
                          (c) => c.id === categoryDiscount.id,
                        )
                          ? 'inverted-danger'
                          : 'inverted-success'
                      "
                      @click.stop.prevent="handleCategoryShareWithReseller(categoryDiscount.id)"
                    >
                      {{
                        contract.custom_fields?.categories?.find(
                          (c) => c.id === categoryDiscount.id,
                        )
                          ? $t("don't share")
                          : $t("share")
                      }}
                      <font-awesome-icon
                        :icon="[
                          'fal',
                          contract.custom_fields?.categories?.find(
                            (c) => c.id === categoryDiscount.id,
                          )
                            ? 'eye-slash'
                            : 'eye',
                        ]"
                        class="ml-1"
                      />
                    </UIButton>

                    <UIButton
                      v-if="showCategoryDiscountId === categoryDiscount.id"
                      @click.self.stop="showCategoryDiscountId = null"
                    >
                      <font-awesome-icon :icon="['fal', 'xmark']" class="ml-auto mr-2" />
                      close
                    </UIButton>
                  </h4>
                  <section
                    v-if="
                      contract.custom_fields?.categories?.find(
                        (c) => c.id === categoryDiscount.id,
                      ) && showCategoryDiscountId === categoryDiscount.id
                    "
                    class="flex flex-wrap items-center space-x-2 space-y-2 bg-gray-100 p-2"
                  >
                    <nav v-if="categoryDiscount.slots.length > 0" class="flex w-full">
                      <button
                        class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                        :class="{
                          'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                            categoryDiscount.mode === 'run',
                        }"
                        @click="categoryDiscount.mode = 'run'"
                      >
                        Runs
                      </button>
                      <button
                        class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                        :class="{
                          'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                            categoryDiscount.mode === 'price',
                        }"
                        @click="categoryDiscount.mode = 'price'"
                      >
                        Price
                      </button>
                    </nav>
                    <!-- <DiscountTile
                      v-for="(discount, index) in categoryDiscount.slots"
                      :key="discount.id"
                      :discount="discount"
                      :is-deletable="index === categoryDiscount.slots.length - 1"
                      :is-infinitable="index === categoryDiscount.slots.length - 1"
                      :is-from-editable="index === 0"
                      @update:discount="handleCategoryDiscountUpdate($event, discount.id, index)"
                      @on-remove-discount="handleCategoryDiscountDelete(discount.id, index)"
                    />
                    <DiscountSlot @add-discount="handleCategoryDiscountAdd(i)" /> -->
                  </section>
                  <section
                    v-if="showCategoryDiscountId === null"
                    class="mt-auto flex justify-between"
                  >
                    <div
                      class="w-auto -skew-x-12 space-x-2 self-start rounded bg-green-100 text-green-500"
                    >
                      <span
                        v-if="categoryDiscount.slots.length > 0"
                        class="skew-x-12 px-4 text-xs font-bold"
                      >
                        -
                        {{
                          categoryDiscount.slots.reduce((max, slot) =>
                            slot.value > max.value ? slot : max,
                          ).value
                        }}
                        <span class="font-mono">%</span>
                      </span>
                    </div>
                    <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end text-sm" />
                  </section>
                  <!-- <div v-else class="italic text-gray-500">
                     this category is not shared with
                   </div> -->
                </div>
              </section>
            </section>
          </section>
        </template>

        <template #side-panel-footer>
          <footer class="sticky bottom-0 flex w-full items-center justify-center p-4">
            <UIButton
              class="px-4 !text-base"
              variant="success"
              @click="handleConfigContractSave(contract)"
            >
              <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
              Save configuration
            </UIButton>
          </footer>
        </template>
      </UIModalSlideIn>
    </Teleport>
  </main>
</template>

<script setup>
// imports
import ContractMessage from "../components/contracts/ContractMessage.vue";
import HandshakeReplyModal from "../components/contracts/HandshakeReplyModal.vue";
// const { fetchSharedCategories } = useMarketplaceRepository();
const { fetchMessages, replyMessage } = useMessagesRepository();
// const { updateContract } = useTenantRepository();
const { handleSuccess, handleError } = useMessageHandler();

// props
const props = defineProps({
  me: {
    type: Object,
    default: () => ({}),
  },
});

// refs
// handshakes
const handshakes = ref([]);
const replyHandshake = ref(null);
const replyStatus = ref(null);
const sendingMessage = ref(false);
const showHandshakeModal = ref(false);

// contract configuration refs
const loading = ref(false);
const contract = ref(null);
const contractDetails = ref(null);
const showContractConfiguration = ref(false);
const sharedCategories = ref([]);
const showCategoryDiscountId = ref(null);

// computed
// handshakes
const pendingHandshakes = computed(() =>
  groupHandshakesBySender(
    handshakes.value.filter((handshake) => handshake?.contract?.st?.name === "pending"),
  ),
);

const acceptedHandshakes = computed(() =>
  groupHandshakesBySender(
    handshakes.value.filter((handshake) => handshake?.contract?.st?.name === "accepted"),
  ),
);

const suspendedHandshakes = computed(() =>
  groupHandshakesBySender(
    handshakes.value.filter((handshake) => handshake?.contract?.st?.name === "suspended"),
  ),
);

const declinedHandshakes = computed(() =>
  groupHandshakesBySender(
    handshakes.value.filter((handshake) => handshake?.contract?.st?.name === "rejected"),
  ),
);

// Lifecycle
onMounted(() => {
  fetchMessages().then((response) => {
    handshakes.value = response;
  });
});

// Methods
// group handshakes by sender, accounting for parent-child relationships
const groupHandshakesBySender = (handshakes) => {
  // First, identify all parent handshakes (those without a parent_id or those that are root items)
  const parentHandshakes = handshakes.filter(
    (handshake) => !handshake.parent_id || handshake.path === handshake.id.toString(),
  );

  // Group by sender
  return parentHandshakes.reduce((acc, handshake) => {
    const existingIndex = acc.findIndex((h) => h.sender_name === handshake.sender_name);

    // Find all child handshakes for this parent
    const childHandshakes = handshakes.filter(
      (h) =>
        h.parent_id === handshake.id ||
        (h.path && h.path.includes(handshake.id.toString()) && h.id !== handshake.id),
    );

    if (existingIndex !== -1) {
      // If sender already exists
      const existing = acc[existingIndex];

      if (!existing.items) {
        existing.items = [{ ...handshake, children: childHandshakes }];
      } else {
        existing.items.push({ ...handshake, children: childHandshakes });
      }
    } else {
      // If this is a new sender
      acc.push({
        ...handshake,
        items:
          childHandshakes.length > 0 ? [{ ...handshake, children: childHandshakes }] : undefined,
      });
    }

    return acc;
  }, []);
};

// const handleGeneralDiscountAdd = () => {
//   if (!contract.value.custom_fields.discount.general.mode) {
//     contract.value.custom_fields.discount.general.mode = "run";
//   }
//   let newDiscount = {};

//   if (contract.value.custom_fields?.discount.general.slots.length >= 1) {
//     newDiscount = {
//       id: contract.value.custom_fields?.discount.general.slots.length + 1,
//       from:
//         contract.value.custom_fields?.discount.general.slots[
//           contract.value.custom_fields?.discount.general.slots.length - 1
//         ].to + 1, // from the previous to + 1
//       to:
//         contract.value.custom_fields?.discount.general.slots[
//           contract.value.custom_fields?.discount.general.slots.length - 1
//         ].to + 200, // to the previous to + 2
//       type: "percentage", // default type
//       value:
//         contract.value.custom_fields?.discount.general.slots[
//           contract.value.custom_fields?.discount.general.slots.length - 1
//         ].value,
//     };
//   } else {
//     newDiscount = {
//       from: 0,
//       to: 100,
//       type: "percentage",
//       value: 10,
//       status: true,
//     };
//   }
//   contract.value.custom_fields?.discount.general.slots.push(newDiscount);
// };

// handle general discount update
// const handleGeneralDiscountUpdate = (discount, idx) => {
//   contract.value.custom_fields.discount.general.slots[idx] = discount;
// };

// const handleGeneralDiscountDelete = (index) => {
//   contract.value.custom_fields.discount.general.slots.splice(index, 1);
// };

// const handleCategoryDiscountUpdate = (discount, id, index) => {
//   const categoryIndex = contract.value.custom_fields.discount.categories.findIndex((c) =>
//     c.slots.some((d) => d.id === id),
//   );
//   contract.value.custom_fields.discount.categories[categoryIndex].slots[index] = discount;
// };

// const handleCategoryDiscountDelete = (id) => {
//   contract.value.custom_fields.discount.categories.forEach((category) => {
//     if (category.slots.some((d) => d.id === id)) {
//       // category.slots = category.slots.splice(category.slots.findIndex((d) => d.id === id) - 1, 1); // this seemed to have some issues
//       category.slots.splice(
//         category.slots.findIndex((d) => d.id === id),
//         1,
//       ); // remove the discount
//     }
//   });
// };

// const handleCategoryDiscountAdd = (i) => {
//   if (
//     !contract.value.custom_fields.discount.categories[i].mode ||
//     contract.value.custom_fields.discount.categories[i].mode === null
//   ) {
//     contract.value.custom_fields.discount.categories[i].mode = "run";
//   }

//   // Add the new discount
//   let newDiscount = {};

//   // If there are existing discounts, add a new one following the previous one
//   if (contract.value.custom_fields?.discount.categories[i].slots.length >= 1) {
//     newDiscount = {
//       id: contract.value.custom_fields?.discount.categories[i].slots.length + 1,
//       from:
//         contract.value.custom_fields?.discount.categories[i].slots[
//           contract.value.custom_fields?.discount.categories[i].slots.length - 1
//         ].to + 1, // from the previous to + 1
//       to:
//         contract.value.custom_fields?.discount.categories[i].slots[
//           contract.value.custom_fields?.discount.categories[i].slots.length - 1
//         ].to + 200, // to the previous to + 2
//       type: "percentage", // default type
//       value:
//         contract.value.custom_fields?.discount.categories[i].slots[
//           contract.value.custom_fields?.discount.categories[i].slots.length - 1
//         ].value,
//     };
//   } else {
//     // If there are no existing discounts, add a new one with default values
//     newDiscount = {
//       from: 0,
//       to: 100,
//       type: "percentage",
//       value: 10,
//       status: true,
//     };
//   }

//   contract.value.custom_fields?.discount.categories[i].slots.push(newDiscount);
// };

const handleSendReply = (message) => {
  sendingMessage.value = true;
  replyMessage(message)
    .then((response) => {
      // TODO: wont work as response.data is not the same as the updated handshake from db (throws error: sender_hostname: ["You cannot reply to your own message."] )
      // handshakes.value = handshakes.value.map((handshake) => {
      //   if (handshake.contract.id === response.data.contract.id) {
      //     handshake = response.data;
      //   }
      //   return handshake;
      // });
      showHandshakeModal.value = false;

      // temp fix to fetch the messages again
      fetchMessages()
        .then((response) => {
          handshakes.value = response;
        })
        .catch((error) => {
          handleError(error);
        });
      handleSuccess(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      sendingMessage.value = false;
    });
};
const handleHandshakeReply = (reply) => {
  replyHandshake.value = reply.handshake;
  replyStatus.value = reply.status;
  showHandshakeModal.value = true;
};

const handleConfigContract = (handshake) => {
  // Reset the state
  showContractConfiguration.value = false;
  contract.value = null;
  loading.value = true;

  // Use nextTick to ensure the state is reset before showing new config
  nextTick(() => {
    // Fetch the contract details
    contract.value = handshake.contract;
    contractDetails.value = {
      sender: handshake.sender_name,
    };

    // Fetch the shared categories
    fetchSharedCategories(handshake.contract.receiver.website_id)
      .then((response) => {
        sharedCategories.value = response;

        // Add the categories to the contract if they don't exist
        if (!contract.value.custom_fields.discount) {
          contract.value.custom_fields.discount = { categories: [] };
        } else if (!contract.value.custom_fields.discount.categories) {
          contract.value.custom_fields.discount.categories = [];
        }
        if (!contract.value.custom_fields.categories) {
          contract.value.custom_fields.categories = [];
          sharedCategories.value.forEach((category) => {
            contract.value.custom_fields.categories.push({ id: category.id });
          });
        }

        // Add the discounts to the categories if they don't exist
        response.forEach((category) => {
          if (!contract.value.custom_fields.discount.categories.some((c) => c.id === category.id)) {
            const categoryDiscount = {
              id: category.id,
              mode: "run",
              status: true,
              slots: [],
            };
            contract.value.custom_fields?.discount.categories.push(categoryDiscount);
          }
        });
      })
      .catch((error) => {
        handleError(error);
      })
      .finally(() => {
        loading.value = false;
      });

    // Add the general discounts if they don't exist
    if (!contract.value.custom_fields?.discount) {
      contract.value.custom_fields = {
        discount: {
          general: {
            mode: "run",
            status: true,
            slots: [],
          },
        },
      };
    } else if (!contract.value.custom_fields.discount.general) {
      contract.value.custom_fields.discount.general = [];
    }

    handshake.contract = contract.value;

    // Show the contract configuration
    showContractConfiguration.value = true;
  });
};

const handleCategoryShareWithReseller = (id) => {
  // const category = sharedCategories.value.find((c) => c.id === id);
  if (contract.value.custom_fields.categories.some((c) => c.id === id)) {
    contract.value.custom_fields.categories.splice(
      contract.value.custom_fields.categories.findIndex((c) => c.id === id),
      1,
    );
  } else {
    contract.value.custom_fields.categories.push({ id: id });
  }
};

// Save the contract configuration
const handleConfigContractSave = (contract) => {
  // Update the contract with the new configuration
  handshakes.value = handshakes.value.map((h) => {
    if (h.contract?.id === contract.id) {
      h.contract = contract;
    }
    return h;
  });

  // Remove categories with no discounts
  contract.custom_fields.discount.categories = contract.custom_fields.discount.categories.filter(
    (c) => c.slots.length > 0,
  );

  // Send the updated contract
  updateContract(contract)
    .then((response) => {
      handleSuccess(response);
      contract.value = null;
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      showContractConfiguration.value = false;
    });
};
</script>

<style scoped>
/* Your styles here */
</style>

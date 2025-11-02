<template>
  <div>
    <main class="mx-auto flex flex-col gap-4 pt-4 md:grid md:grid-cols-4">
      <section class="space-y-4 rounded border border-blue-400 bg-gray-100 p-4 dark:bg-gray-900">
        <h2
          class="sticky -top-4 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
        >
          <font-awesome-icon :icon="['fal', 'hand-holding-hand']" class="mr-2" />
          {{ $t("Pending handshakes") }}
        </h2>
        <input
          v-model="searchPending"
          type="text"
          class="input py-1"
          :placeholder="$t('Filter pending handshakes')"
        />
        <ContractMessage
          v-for="handshake in pendingHandshakes"
          :key="handshake.sender_name"
          :handshake="handshake"
          :me="me"
          @reply="handleHandshakeReply"
        />
      </section>
      <section class="space-y-4 rounded border border-green-400 bg-gray-100 p-4 dark:bg-gray-900">
        <h2
          class="sticky -top-4 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
        >
          <font-awesome-icon :icon="['fal', 'handshake']" class="mr-2" />
          {{ $t("Accepted handshakes") }}
        </h2>
        <input
          v-model="searchAccepted"
          type="text"
          class="input py-1"
          :placeholder="$t('Filter accepted handshakes')"
        />
        <ContractMessage
          v-for="handshake in acceptedHandshakes"
          :key="handshake.sender_name"
          :handshake="handshake"
          :me="me"
          @reply="handleHandshakeReply"
          @config-contract="handleConfigContract"
        />
      </section>
      <section class="space-y-4 rounded border border-amber-400 bg-gray-100 p-4 dark:bg-gray-900">
        <h2
          class="sticky -top-4 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
        >
          <font-awesome-icon :icon="['fal', 'handshake-slash']" class="mr-2" />
          {{ $t("Suspended handshakes") }}
        </h2>
        <input
          v-model="searchSuspended"
          type="text"
          class="input py-1"
          :placeholder="$t('Filter suspended handshakes')"
        />
        <ContractMessage
          v-for="handshake in suspendedHandshakes"
          :key="handshake.sender_name"
          :handshake="handshake"
          :me="me"
          @reply="handleHandshakeReply"
        />
      </section>
      <section class="space-y-4 rounded border border-red-400 bg-gray-100 p-4 dark:bg-gray-900">
        <h2
          class="sticky -top-4 bg-gray-100 text-lg font-bold uppercase tracking-wide text-gray-500 dark:bg-gray-900"
        >
          <font-awesome-icon :icon="['fal', 'handshake-slash']" class="mr-2" />
          {{ $t("Declined handshakes") }}
        </h2>
        <input
          v-model="searchDeclined"
          type="text"
          class="input py-1"
          :placeholder="$t('Filter declined handshakes')"
        />
        <ContractMessage
          v-for="handshake in declinedHandshakes"
          :key="handshake.sender_name"
          :handshake="handshake"
          :me="me"
          @reply="handleHandshakeReply"
        />
      </section>
    </main>

    <Teleport to="body">
      <HandshakeReplyModal
        v-if="showHandshakeModal && replyHandshake !== null && replyStatus !== null"
        :handshake="replyHandshake"
        :status="replyStatus"
        :is-loading="sendingMessage"
        @on-send="handleSendReply($event)"
        @on-close="showHandshakeModal = false"
      />
      <SidePanel
        v-if="showContractConfiguration && contract !== null"
        width="w-11/12 lg:w-2/3"
        :full-height="true"
        class="relative h-full"
        @close="((showContractConfiguration = false), (contract = null))"
      >
        <template #side-panel-header>
          <header class="sticky top-0 bg-white p-4 dark:bg-gray-700">
            <h2 class="text-lg font-bold uppercase tracking-wide">
              {{ $t("Handshake contract configuration") }} - {{ contractDetails.sender }}
            </h2>
            <p class="mt-1 text-sm italic text-gray-500">
              {{
                // prettier-ignore
                $t("You can apply a discount to your reseller and pick what categories you want to share, possibly with a nice specific applied discount.")
              }}
            </p>
          </header>
        </template>
        <template #side-panel-content>
          <section class="rounded bg-gray-100 p-4 dark:bg-gray-800">
            <h3 class="font-bold uppercase tracking-wide">
              {{ $t("General") }}
            </h3>
            <h4 class="my-2 text-sm">
              {{ $t("Discounts for") }} <strong>{{ contractDetails.sender }}</strong>
            </h4>
            <section class="flex flex-wrap items-center space-x-2 space-y-2">
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
                    {{ $t("Runs") }}
                  </button>
                  <button
                    class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                    :class="{
                      'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                        contract.custom_fields?.discount?.general.mode === 'price',
                    }"
                    @click="contract.custom_fields.discount.general.mode = 'price'"
                  >
                    {{ $t("Price") }}
                  </button>
                </nav>
                <DiscountTile
                  v-for="(discount, index) in contract.custom_fields?.discount.general.slots"
                  :key="discount.id"
                  :discount="discount"
                  :is-deletable="
                    index === contract.custom_fields?.discount?.general.slots.length - 1
                  "
                  :type="'general'"
                  :is-infinitable="
                    index === contract.custom_fields?.discount?.general.slots.length - 1
                  "
                  :is-from-editable="index === 0"
                  @update:discount="handleGeneralDiscountUpdate($event, index)"
                  @on-remove-discount="handleGeneralDiscountDelete(index)"
                />
              </template>
              <DiscountSlot key="DiscountSlot" @add-discount="handleGeneralDiscountAdd" />
            </section>
            <section v-if="loading" class="mt-4">
              <UIListSkeleton
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
              />
            </section>
            <section v-else class="flex flex-col">
              <h3 class="mt-8 font-bold uppercase tracking-wide">
                {{ $t("your shared categories") }}
              </h3>
              <section class="relative grid grid-cols-3 gap-4 sm:grid-cols-4">
                <div
                  v-for="(categoryDiscount, i) in contract.custom_fields?.discount?.categories"
                  :key="categoryDiscount.id"
                  class="cursor-pointer select-none rounded bg-white p-4 shadow-md hover:shadow-lg dark:bg-gray-700 dark:text-white dark:shadow-gray-800"
                  @click="openCategoryDiscountModal(categoryDiscount, i)"
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
                    <div
                      class="ml-2 normal-case first-letter:uppercase"
                      :class="
                        contract.custom_fields?.categories?.find(
                          (c) => c.id === categoryDiscount.id,
                        )
                          ? 'text-green-500'
                          : 'text-red-500'
                      "
                    >
                      {{
                        contract.custom_fields?.categories?.find(
                          (c) => c.id === categoryDiscount.id,
                        )
                          ? $t("shared")
                          : $t("not shared")
                      }}
                      <font-awesome-icon
                        :icon="[
                          'fal',
                          contract.custom_fields?.categories?.find(
                            (c) => c.id === categoryDiscount.id,
                          )
                            ? 'eye'
                            : 'eye-slash',
                        ]"
                        class="ml-1"
                      />
                    </div>
                  </h4>
                  <section class="mt-auto flex justify-between">
                    <div
                      class="w-auto -skew-x-12 space-x-2 self-start rounded bg-green-100 text-green-500"
                    >
                      <span
                        v-if="
                          categoryDiscount.slots.length > 0 &&
                          categoryDiscount.slots.reduce((max, slot) =>
                            slot.value > max.value ? slot : max,
                          ).type === 'percentage'
                        "
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
                      <span
                        v-if="
                          categoryDiscount.slots.length > 0 &&
                          categoryDiscount.slots.reduce((max, slot) =>
                            slot.value > max.value ? slot : max,
                          ).type === 'fixed'
                        "
                        class="skew-x-12 px-4 text-xs font-bold"
                      >
                        -
                        {{
                          formatCurrency(
                            categoryDiscount.slots.reduce((max, slot) =>
                              slot.value > max.value ? slot : max,
                            ).value / 1000,
                          )
                        }}
                      </span>
                    </div>
                    <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end text-sm" />
                  </section>
                  <!-- <div v-else class="italic text-gray-500">
                      {{ $t("this category is not shared with") }}
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
              {{ $t("Save configuration") }}
            </UIButton>
          </footer>
        </template>
      </SidePanel>
      <ConfirmationModal
        v-if="showAddDiscountModal && selectedCategoryData"
        classes="w-11/12 lg:w-2/3 xl:w-1/2"
        @on-close="closeAddDiscountModal"
      >
        <template #modal-header>
          <h3 class="text-lg font-bold">
            {{ $t("Manage Discounts") }} -
            {{
              $display_name(
                sharedCategories.find((c) => c.id === selectedCategoryData?.id)?.display_name,
              )
            }}
          </h3>
        </template>
        <template #modal-body>
          <div class="space-y-4">
            <!-- Category Sharing Toggle -->
            <div class="flex items-center justify-between rounded bg-gray-100 p-3 dark:bg-gray-800">
              <span class="text-sm font-medium">{{ $t("Share with reseller") }}</span>
              <UIButton
                :variant="
                  contract.custom_fields?.categories?.find((c) => c.id === selectedCategoryData?.id)
                    ? 'inverted-danger'
                    : 'inverted-success'
                "
                @click="handleCategoryShareWithReseller(selectedCategoryData?.id)"
              >
                {{
                  contract.custom_fields?.categories?.find((c) => c.id === selectedCategoryData?.id)
                    ? $t("don't share")
                    : $t("share")
                }}
                <font-awesome-icon
                  :icon="[
                    'fal',
                    contract.custom_fields?.categories?.find(
                      (c) => c.id === selectedCategoryData?.id,
                    )
                      ? 'eye-slash'
                      : 'eye',
                  ]"
                  class="ml-1"
                />
              </UIButton>
            </div>

            <!-- Discount Management Section -->
            <div
              v-if="
                contract.custom_fields?.categories?.find((c) => c.id === selectedCategoryData?.id)
              "
              class="space-y-4"
            >
              <!-- Mode Selection -->
              <nav v-if="selectedCategoryData.slots.length > 0" class="flex w-full">
                <button
                  class="w-full rounded-l bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                  :class="{
                    'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                      selectedCategoryData.mode === 'run',
                  }"
                  @click="selectedCategoryData.mode = 'run'"
                >
                  {{ $t("Runs") }}
                </button>
                <button
                  class="w-full rounded-r bg-gray-200 px-2 py-1 text-sm text-black transition-colors duration-150 hover:bg-gray-300 dark:bg-gray-600 dark:text-white"
                  :class="{
                    'border-theme-600 bg-theme-400 text-themecontrast-400 shadow-inner hover:bg-theme-500 dark:border-theme-600 dark:bg-theme-400 dark:hover:bg-theme-500':
                      selectedCategoryData.mode === 'price',
                  }"
                  @click="selectedCategoryData.mode = 'price'"
                >
                  {{ $t("Price") }}
                </button>
              </nav>

              <!-- Discount Tiles -->
              <div class="flex flex-wrap space-x-4 space-y-4">
                <DiscountTile
                  v-for="(discount, index) in selectedCategoryData.slots"
                  :key="discount.id"
                  :discount="discount"
                  :is-deletable="index === selectedCategoryData.slots.length - 1"
                  :type="'category'"
                  :is-infinitable="index === selectedCategoryData.slots.length - 1"
                  :is-from-editable="index === 0"
                  @update:discount="
                    handleCategoryDiscountUpdate($event, selectedCategoryData.id, index)
                  "
                  @on-remove-discount="handleCategoryDiscountDelete(selectedCategoryData.id, index)"
                />

                <!-- Add Discount Button -->
                <DiscountSlot @add-discount="handleCategoryDiscountAdd(selectedCategoryIndex)" />
              </div>
            </div>

            <div v-else class="p-4 text-center italic text-gray-500">
              {{ $t("This category is not shared with the reseller") }}
            </div>
          </div>
        </template>
        <template #confirm-button>
          <UIButton variant="theme" class="px-4 py-1 !text-sm" @click="closeAddDiscountModal">
            {{ $t("Done") }}
          </UIButton>
        </template>
      </ConfirmationModal>
    </Teleport>
  </div>
</template>

<script setup>
// imports
const { fetchSharedCategories } = useMarketplaceRepository();
const { fetchMessages, replyMessage } = useMessagesRepository();
const { updateContract } = useTenantRepository();
const { handleSuccess, handleError } = useMessageHandler();
const { formatCurrency } = useMoney();

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

// discount modal refs
const showAddDiscountModal = ref(false);
const selectedCategoryIndex = ref(null);
const selectedCategoryData = ref(null);

// search
const searchPending = ref("");
const searchAccepted = ref("");
const searchSuspended = ref("");
const searchDeclined = ref("");

// computed
// handshakes
const pendingHandshakes = computed(() =>
  buildMessageTree(
    handshakes.value.filter(
      (handshake) =>
        handshake?.contract?.st?.name === "pending" &&
        handshake?.to !== "cec" &&
        (handshake?.sender_name ?? "")
          .toLowerCase()
          .includes((searchPending?.value ?? "").toLowerCase()),
    ),
  ),
);

const acceptedHandshakes = computed(() =>
  buildMessageTree(
    handshakes.value.filter(
      (handshake) =>
        handshake?.contract?.st?.name === "accepted" &&
        handshake?.to !== "cec" &&
        (handshake?.sender_name ?? "")
          .toLowerCase()
          .includes((searchAccepted?.value ?? "").toLowerCase()),
    ),
  ),
);

const suspendedHandshakes = computed(() =>
  buildMessageTree(
    handshakes.value.filter(
      (handshake) =>
        handshake?.contract?.st?.name === "suspended" &&
        handshake?.to !== "cec" &&
        (handshake?.sender_name ?? "")
          .toLowerCase()
          .includes((searchSuspended?.value ?? "").toLowerCase()),
    ),
  ),
);

const declinedHandshakes = computed(() =>
  buildMessageTree(
    handshakes.value.filter(
      (handshake) =>
        handshake?.contract?.st?.name === "rejected" &&
        handshake?.to !== "cec" &&
        (handshake?.sender_name ?? "")
          .toLowerCase()
          .includes((searchDeclined?.value ?? "").toLowerCase()),
    ),
  ),
);

const type = useRoute().query.type;

// Lifecycle
onMounted(() => {
  fetchMessages(type).then((response) => {
    handshakes.value = response;
  });
});

// Methods
// group handshakes by sender
// const groupHandshakesBySender = (handshakes) => {
//   return handshakes.reduce((acc, handshake) => {
//     const existing = acc.find((h) => h.sender_name === handshake.sender_name);
//     if (existing) {
//       if (!existing.items) {
//         existing.items = [{ ...existing }];
//       }
//       existing.items.push({ ...handshake });
//     } else {
//       acc.push({ ...handshake });
//     }
//     return acc;
//   }, []);
// };

const buildMessageTree = (messages) => {
  // Create a map for quick lookup of messages by id
  const messageMap = new Map();

  // First pass: Create all nodes and add children array
  messages.forEach((message) => {
    messageMap.set(message.id, {
      ...message,
      items: [message],
    });
  });

  // Second pass: Build the tree structure
  const rootMessages = [];

  messages.forEach((message) => {
    const messageNode = messageMap.get(message.id);

    if (message.parent_id === null || message.parent_id === undefined) {
      // This is a root message (no parent)
      rootMessages.push(messageNode);
    } else {
      // This is a child message - find its parent and add it to children
      const parentNode = messageMap.get(message.parent_id);
      if (parentNode) {
        parentNode.items.push(messageNode);
      } else {
        // Parent not found - treat as root message
        console.warn(`Parent message ${message.parent_id} not found for message ${message.id}`);
        rootMessages.push(messageNode);
      }
    }
  });

  return rootMessages;
};

const handleGeneralDiscountAdd = () => {
  if (!contract.value.custom_fields.discount.general.mode) {
    contract.value.custom_fields.discount.general.mode = "run";
  }
  let newDiscount = {};

  if (contract.value.custom_fields?.discount.general.slots.length >= 1) {
    newDiscount = {
      id: contract.value.custom_fields?.discount.general.slots.length + 1,
      from:
        contract.value.custom_fields?.discount.general.slots[
          contract.value.custom_fields?.discount.general.slots.length - 1
        ].to + 1, // from the previous to + 1
      to:
        contract.value.custom_fields?.discount.general.slots[
          contract.value.custom_fields?.discount.general.slots.length - 1
        ].to + 200, // to the previous to + 2
      type: "percentage", // default type
      value:
        contract.value.custom_fields?.discount.general.slots[
          contract.value.custom_fields?.discount.general.slots.length - 1
        ].value,
    };
  } else {
    newDiscount = {
      id: 1,
      from: 0,
      to: 10000,
      type: "percentage",
      value: 10,
      status: true,
    };
  }
  contract.value.custom_fields?.discount.general.slots.push(newDiscount);
};

// handle general discount update
const handleGeneralDiscountUpdate = (discount, idx) => {
  if (contract.value.custom_fields.discount.general.slots[idx]) {
    // Create a new array with the updated discount to ensure reactivity
    const updatedSlots = [...contract.value.custom_fields.discount.general.slots];
    updatedSlots[idx] = { ...discount };

    // Update the entire slots array to trigger reactivity
    contract.value.custom_fields.discount.general.slots = updatedSlots;
  }
};

const handleGeneralDiscountDelete = (index) => {
  contract.value.custom_fields.discount.general.slots.splice(index, 1);
};

const handleCategoryDiscountUpdate = (discount, categoryId, index) => {
  const categoryIndex = contract.value.custom_fields.discount.categories.findIndex(
    (c) => c.id === categoryId,
  );

  if (
    categoryIndex !== -1 &&
    contract.value.custom_fields.discount.categories[categoryIndex].slots[index]
  ) {
    // Create a new array with the updated discount to ensure reactivity
    const updatedSlots = [...contract.value.custom_fields.discount.categories[categoryIndex].slots];
    updatedSlots[index] = { ...discount, id: index + 1 };

    // Update the entire slots array to trigger reactivity
    contract.value.custom_fields.discount.categories[categoryIndex].slots = updatedSlots;
  }
};

const handleCategoryDiscountDelete = (categoryId, index) => {
  const categoryIndex = contract.value.custom_fields.discount.categories.findIndex(
    (c) => c.id === categoryId,
  );

  if (
    categoryIndex !== -1 &&
    contract.value.custom_fields.discount.categories[categoryIndex].slots[index]
  ) {
    contract.value.custom_fields.discount.categories[categoryIndex].slots.splice(index, 1);
  }
};

const handleCategoryDiscountAdd = (i) => {
  if (
    !contract.value.custom_fields.discount.categories[i].mode ||
    contract.value.custom_fields.discount.categories[i].mode === null
  ) {
    contract.value.custom_fields.discount.categories[i].mode = "run";
  }

  // Add the new discount
  let newDiscount = {};

  // If there are existing discounts, add a new one following the previous one
  if (contract.value.custom_fields?.discount.categories[i].slots.length >= 1) {
    newDiscount = {
      id: contract.value.custom_fields?.discount.categories[i].slots.length + 1,
      from:
        contract.value.custom_fields?.discount.categories[i].slots[
          contract.value.custom_fields?.discount.categories[i].slots.length - 1
        ].to + 1, // from the previous to + 1
      to:
        contract.value.custom_fields?.discount.categories[i].slots[
          contract.value.custom_fields?.discount.categories[i].slots.length - 1
        ].to + 200, // to the previous to + 2
      type: "percentage", // default type
      value:
        contract.value.custom_fields?.discount.categories[i].slots[
          contract.value.custom_fields?.discount.categories[i].slots.length - 1
        ].value,
    };
  } else {
    // If there are no existing discounts, add a new one with default values
    newDiscount = {
      id: 1,
      from: 0,
      to: 100,
      type: "percentage",
      value: 10,
      status: true,
    };
  }

  contract.value.custom_fields?.discount.categories[i].slots.push(newDiscount);
};

// Modal methods for managing category discounts
const openCategoryDiscountModal = (categoryData, categoryIndex) => {
  selectedCategoryData.value = categoryData;
  selectedCategoryData.value.slots.forEach((discount, index) => {
    discount.id = index + 1;
  });
  selectedCategoryIndex.value = categoryIndex;
  showAddDiscountModal.value = true;
};

const closeAddDiscountModal = () => {
  showAddDiscountModal.value = false;
  selectedCategoryIndex.value = null;
  selectedCategoryData.value = null;
};

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
      showHandshakeModal.value = false;
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

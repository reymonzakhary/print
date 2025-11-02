<template>
  <div class="h-full py-4">
    <ProducerUpgrade
      v-if="!me?.supplier"
      @send-email="
        sendMessage($event).then((response) =>
          handleSuccess(response).catch((error) => handleError(error)),
        )
      "
    />

    <article v-else class="grid justify-center gap-4 py-4 lg:grid-cols-2">
      <section
        v-if="producer?.producerConfig"
        class="w-full rounded bg-white p-4 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
      >
        <h1 class="text-lg font-bold uppercase tracking-wide">{{ producer.company_name }}</h1>
        <h2 class="text-base font-bold uppercase tracking-wide">
          {{ $t("external producer configuration") }}
        </h2>
        <p class="mb-4 text-sm italic text-gray-500">
          {{ $t("Connect to your producer API!") }}
        </p>
        <!-- {{ producer.producerConfig }} -->
        <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("login") }}</h3>

        <div class="mb-8 grid gap-4 rounded bg-gray-50 p-4 dark:bg-gray-800 lg:grid-cols-2">
          <span>
            <label
              class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              for="name"
            >
              {{ $t("plugin external endpoint") }}
            </label>
            <div class="flex">
              <UIInputText
                class="!border-r-0"
                :model-value="producer.producerConfig.plugin_external_endpoint"
                :name="$t('plugin external endpoint')"
                :placeholder="$t('external endpoint')"
                :prefix="['fal', 'door-open']"
                @update:model-value="producer.producerConfig.plugin_external_endpoint = $event"
              />
            </div>
          </span>
          <span>
            <label
              class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              for="name"
            >
              {{ $t("plugin external credentials") }}
            </label>
            <div class="flex">
              <UIInputText
                class="!border-r-0"
                :model-value="producer.producerConfig.plugin_external_username"
                :name="$t('config plugin_external_username')"
                :placeholder="$t('username')"
                :prefix="['fal', 'user']"
                @update:model-value="producer.producerConfig.plugin_external_username = $event"
              />
              <UIInputText
                class="!border-r-0"
                :model-value="producer.producerConfig.plugin_external_password"
                :name="$t('config plugin_external_password')"
                type="password"
                placeholder="*****"
                :prefix="['fal', 'key']"
                @update:model-value="producer.producerConfig.plugin_external_password = $event"
              />
            </div>
          </span>
        </div>

        <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("configuration") }}</h3>
        <div class="grid gap-4 lg:grid-cols-2">
          <span>
            <label
              class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              for="name"
            >
              <font-awesome-icon
                :icon="['fal', 'file-contract']"
                fixed-width
                class="text-gray-500 dark:text-gray-400"
              />
              {{ $t("config name") }}
            </label>
            <div class="flex">
              <UIInputText
                input-class="rounded-none rounded-l-md"
                :model-value="producer.producerConfig.name"
                :name="$t('config name')"
                :placeholder="$t('config name')"
                @update:model-value="producer.producerConfig.name = $event"
              />
              <UIInputText
                input-class="rounded-none rounded-r-md"
                :model-value="producer.producerConfig.version"
                :name="$t('config version')"
                :placeholder="$t('config version')"
                affix="v"
                @update:model-value="producer.producerConfig.version = $event"
              />
            </div>
          </span>

          <span>
            <label
              class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              for="name"
            >
              <font-awesome-icon
                :icon="['fal', 'door-open']"
                fixed-width
                class="text-gray-500 dark:text-gray-400"
              />
              {{ $t("prefix and port") }}
            </label>

            <div class="flex">
              <UIInputText
                input-class="rounded-none rounded-l-md"
                :model-value="producer.producerConfig.name"
                :name="$t('config prefix')"
                prefix="prefix"
                :placeholder="$t('config prefix')"
                @update:model-value="producer.producerConfig.name = $event"
              />
              <UIInputText
                input-class="rounded-none rounded-r-md"
                :model-value="producer.producerConfig.port"
                :name="$t('config port')"
                prefix=":"
                :placeholder="$t('name')"
                @update:model-value="producer.name = $event"
              />
            </div>
          </span>

          <span v-for="route in producer.producerConfig.routes" :key="route.route">
            <label
              class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
              for="name"
            >
              <font-awesome-icon
                :icon="['fal', 'route']"
                fixed-width
                class="text-gray-500 dark:text-gray-400"
              />
              {{ $t("route") }}
            </label>

            <div class="flex">
              <UISelector
                :model-value="route.method"
                class="!w-24 rounded-r-none bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                :options="[
                  { value: 'get', label: 'GET' },
                  { value: 'put', label: 'PUT' },
                  { value: 'post', label: 'POST' },
                ]"
                @update:model-value="route.method = $event"
              />
              <UIInputText
                input-class="rounded-none rounded-r-md"
                :model-value="route.route"
                :name="$t('config name')"
                :placeholder="$t('config name')"
                @update:model-value="route.route = $event"
              />
            </div>
          </span>

          <div>
            <UIButton
              class="mt-6 flex !w-auto !py-1 !text-base"
              variant="inverted-success"
              @click="producer.producerConfig.routes.push({ method: 'get', route: '/' })"
            >
              <font-awesome-icon :icon="['fal', 'plus']" class="mr-2" />
              {{ $t("add route") }}
            </UIButton>
          </div>
        </div>

        <div class="mt-8 border-t pt-4">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wide">
              {{ $t("external categories") }}
            </h3>
            <div>
              <UIButton
                class="mr-2 px-4 !text-base"
                :disabled="fetchingCats"
                variant="link"
                @click="getExtCats()"
              >
                <font-awesome-icon :icon="['fal', 'box-full']" class="mr-2" />
                {{ fetchingCats ? $t("fetching categories") : $t("get categories") }}
                <font-awesome-icon
                  v-if="fetchingCats"
                  :icon="['fal', 'spinner-third']"
                  spin
                  class="mr-2"
                />
              </UIButton>
              <UIButton
                class="px-4 !text-base"
                :disabled="saving"
                variant="success"
                @click="saveConfig()"
              >
                <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                {{ saving ? $t("Saving") : $t("Save") }}
                <font-awesome-icon
                  v-if="saving"
                  :icon="['fal', 'spinner-third']"
                  spin
                  class="mr-2"
                />
              </UIButton>
            </div>
          </div>

          <div
            v-if="selectedExtCats.length > 0"
            class="mt-4 flex items-center justify-between rounded bg-gray-100 p-4"
          >
            <ol class="ml-4 list-disc">
              <li v-for="cat in selectedExtCats" :key="cat">{{ cat }}</li>
            </ol>
            <font-awesome-icon :icon="['fal', 'chevron-right']" class="text-2xl" fixed-width />
            <UIButton
              class="mx-4 px-4 !text-base"
              :disabled="syncingCats"
              variant="theme"
              @click="syncExtCats()"
            >
              <font-awesome-icon :icon="['fal', 'box-open']" class="mr-2" />
              <font-awesome-icon :icon="['fal', 'shapes']" class="mr-2" />
              {{ syncingCats ? $t("syncing") : $t("Retreive boxes, options and prices") }}
              <font-awesome-icon
                v-if="syncingCats"
                :icon="['fal', 'spinner-third']"
                spin
                class="mr-2"
              />
            </UIButton>
          </div>
        </div>

        <div v-if="extCats.length > 0" class="pt-4">
          <p class="text-sm italic text-gray-500">
            {{ $t("Select the categories you want to sync with your producer") }}
          </p>

          <p class="text-sm italic text-gray-500">
            {{ $t("for performance select 2 or 3 categories") }}
          </p>
          <!-- Filterable list with checkboxes -->
          <div class="mt-4">
            <input
              v-model="filterText"
              type="text"
              placeholder="Search categories..."
              class="w-full rounded-md border border-gray-300 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            />
            <div class="mt-4 space-y-2">
              <div v-for="cat in filteredExtCats" :key="cat.id" class="flex items-center gap-2">
                <input
                  :id="`extCat-${cat.sku}`"
                  v-model="selectedExtCats"
                  type="checkbox"
                  :value="cat.sku"
                  class="h-4 w-4 rounded border-gray-300 text-theme-400 focus:ring-theme-500 dark:border-gray-700 dark:focus:ring-theme-300"
                />
                <label :for="`extCat-${cat.sku}`" class="text-sm text-gray-700 dark:text-gray-300">
                  {{ cat.titlePlural }}
                </label>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section
        v-if="producer"
        class="w-full rounded bg-white p-4 shadow-md shadow-gray-200 dark:bg-gray-700 dark:shadow-gray-900"
      >
        <h1 class="text-lg font-bold uppercase tracking-wide">{{ producer.company_name }}</h1>
        <h2 class="text-base font-bold uppercase tracking-wide">
          {{ $t("marketing page information") }}
        </h2>
        <p class="mb-4 text-sm italic text-gray-500">
          {{ $t("Showcase your brand and products to potential resellers here!") }}
        </p>

        <div class="divide-y dark:divide-gray-800">
          <section class="mb-8">
            <h3 class="text-sm font-bold uppercase tracking-wide">{{ $t("company") }}</h3>

            <div class="mt-4 grid gap-4 lg:grid-cols-4">
              <span class="col-span-2">
                <label
                  class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
                  for="name"
                >
                  <font-awesome-icon
                    :icon="['fal', 'fingerprint']"
                    fixed-width
                    class="text-gray-500 dark:text-gray-400"
                  />
                  {{ $t("marketing title") }}
                </label>
                <UIInputText
                  :model-value="producer.page_title"
                  :name="$t('marketing title')"
                  :placeholder="$t('what makes your company unique in our marketplace?')"
                  @update:model-value="producer.page_title = $event"
                />
              </span>

              <span class="col-span-2">
                <label
                  class="text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
                  for="name"
                >
                  <font-awesome-icon
                    :icon="['fal', 'fingerprint']"
                    fixed-width
                    class="text-gray-500 dark:text-gray-400"
                  />
                  {{ $t("feature image") }}
                </label>

                <UIImageSelector
                  :selected-image="selectedImage"
                  disk="assets"
                  @on-image-select="producer.page_media = [$event]"
                  @on-image-remove="producer.page_media = []"
                />
              </span>

              <span class="col-span-4 mt-4">
                <label
                  class="mt-8 text-xs font-bold uppercase tracking-wide text-gray-600 dark:text-gray-400"
                  for="name"
                >
                  <font-awesome-icon
                    :icon="['fal', 'fingerprint']"
                    fixed-width
                    class="text-gray-500 dark:text-gray-400"
                  />
                  {{ $t("marketing content") }}
                </label>
                <UITextArea
                  :model-value="producer.page_description"
                  :max-length="500"
                  @update:model-value="producer.page_description = $event"
                />
              </span>
            </div>

            <section class="mt-8 flex items-center justify-center space-x-4">
              <UIButton
                to="/manage/tenant-settings/producer-marketingpage-preview"
                variant="link"
                class="px-4 !text-base"
              >
                <font-awesome-icon :icon="['fal', 'magnifying-glass-waveform']" class="mr-2" />
                {{ $t("Preview page") }}
              </UIButton>

              <UIButton
                class="px-4 !text-base"
                :disabled="saving"
                variant="success"
                @click="save()"
              >
                <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
                {{ saving ? $t("Saving") : $t("Save") }}
                <font-awesome-icon
                  v-if="saving"
                  :icon="['fal', 'spinner-third']"
                  spin
                  class="mr-2"
                />
              </UIButton>
            </section>
          </section>

          <section class="my-8">
            <h3 class="mt-8 text-sm font-bold uppercase tracking-wide">
              {{ $t("shared categories") }}
            </h3>

            <div class="relative flex w-full bg-gray-50 p-4 dark:bg-gray-800">
              <section class="relative grid w-full grid-cols-4 gap-4">
                <div
                  v-for="category in producer.shared_categories"
                  :key="category._id"
                  class="relative z-0 flex h-36 cursor-pointer flex-col justify-between rounded bg-white p-4 shadow-md shadow-gray-200 transition-all hover:shadow-lg dark:bg-gray-700 dark:text-white dark:shadow-gray-900"
                  @click="categoryDetails = category"
                >
                  <span class="self-start font-bold">
                    {{ $display_name(category.display_name) }}
                  </span>

                  <section class="mt-auto flex justify-between">
                    <!-- <div class="w-auto space-x-2 self-start rounded">
                      <span class="text-sm font-bold text-green-500">
                        {{ Math.floor(Math.random() * 100) }}
                      </span>
                      <span class="">{{ $t("tenants connected") }}</span>
                    </div> -->
                    <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end" />
                  </section>
                </div>
              </section>

              <!-- <section
                v-if="categoryDetails"
                class="absolute z-10 -mx-4 h-auto w-full rounded border-2 border-green-500 bg-white p-4 shadow-lg dark:bg-gray-700 dark:shadow-gray-800"
              >
                <span class="flex justify-between self-start font-bold">
                  {{ categoryDetails }}
                  <UIButton
                    variant="theme"
                    class="absolute right-4 top-4"
                    @click.prevent.stop="categoryDetails = false"
                  >
                    <font-awesome-icon :icon="['fal', 'xmark']" class="mr-2" />
                    {{ $t("close") }}
                  </UIButton>
                </span>

                <section class="mt-auto flex justify-between">
                  <div class="w-auto space-x-2 self-start rounded">
                    <span class="text-sm font-bold text-green-500">
                      {{ Math.floor(Math.random() * 100) }}
                    </span>
                    <span class="">{{ $t("tenants connected") }}</span>
                  </div>
                  <font-awesome-icon :icon="['fal', 'arrow-right']" class="self-end" />
                </section>

                <div class="flex h-full flex-col items-center justify-center">
                  <h4 class="text-lg font-bold">{{ $t("linked tenants") }}</h4>

                  <ul class="mt-4">
                    <li v-for="tenant in categoryDetails.tenants" :key="tenant.id">
                      <span>{{ tenant.name }}</span>
                    </li>
                  </ul>
                </div>
              </section> -->
            </div>
          </section>
        </div>
      </section>
    </article>
  </div>
</template>

<script setup>
const props = defineProps({
  tenant: {
    type: Object,
    required: true,
  },
  me: {
    type: Object,
    required: true,
  },
  saving: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(["update:tenant"]);

// Imports
const {
  saveExternalProducerConfig,
  fetchExternalProducerCategories,
  syncExternalProducerCategories,
} = useTenantRepository();
const { sendMessage } = useMessagesRepository();
const { handleSuccess, handleError } = useMessageHandler();

// data
const producer = ref(props.tenant);
const extCats = ref([]);
const filterText = ref("");
const selectedExtCats = ref([]);
const fetchingCats = ref(false);
const syncingCats = ref(false);

const selectedImage = computed(() => {
  if (!producer.value?.page_media?.length) return null;

  const firstMedia = producer.value.page_media[0];
  return firstMedia?.path || firstMedia;
});

watch(
  () => props.tenant,
  (newValue) => {
    producer.value = { ...newValue };
  },
  { deep: true },
);

// lifecycle
onMounted(async () => {
  // Check if external producer categories exist in localStorage
  const storedCategories = localStorage.getItem("externalProducerCategories");
  if (storedCategories) {
    try {
      extCats.value = JSON.parse(storedCategories);
    } catch (error) {
      console.error("Error parsing stored categories:", error);
    }
  } else {
    if (props.me.supplier && props.tenant.external) {
      // Fetch external producer categories if not found in localStorage
      getExtCats();
    }
  }
});

// Computed property to filter extCats based on the search text
const filteredExtCats = computed(() => {
  return extCats.value.filter((cat) =>
    cat.sku.toLowerCase().includes(filterText.value.toLowerCase()),
  );
});

// methods
function save() {
  emit("update:tenant", { ...producer.value });
}

function saveConfig() {
  saving.value = true;
  saveExternalProducerConfig(producer.value.producerConfig)
    .then((response) => {
      handleSuccess(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      saving.value = false;
    });
}

function getExtCats() {
  fetchingCats.value = true;
  fetchExternalProducerCategories()
    .then((response) => {
      extCats.value = response;
      handleSuccess(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      fetchingCats.value = false;
    });
}

function syncExtCats() {
  syncingCats.value = true;
  syncExternalProducerCategories({
    skus: selectedExtCats.value,
  })
    .then((response) => {
      handleSuccess(response);
    })
    .catch((error) => {
      handleError(error);
    })
    .finally(() => {
      fetchingCats.value = false;
      syncingCats.value = false;
      filterText.value = "";
    });
}
</script>

<style scoped>
/* Your styles here */
</style>

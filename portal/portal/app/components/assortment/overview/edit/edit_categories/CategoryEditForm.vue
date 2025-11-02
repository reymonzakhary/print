<template>
  <div>
    <div class="mx-auto lg:w-2/3 xl:w-1/2" v-if="!wizardMode">
      <h2 class="w-full text-lg font-bold tracking-wide">
        <span class="icon">
          <font-awesome-icon :icon="['fal', 'memo-circle-info']" fixed-width />
        </span>
        {{ $t("Information about your category") }}
      </h2>
      <p class="mb-4 text-sm text-gray-500">
        <span class="italic">{{
          // prettier-ignore
          $t("Main information about your category, like name, description and visibility")
        }}</span>
      </p>
    </div>

    <div
      class="mx-auto w-full rounded-md bg-white p-4 shadow-md dark:bg-gray-700"
      :class="{
        '!mt-4 rounded-none !p-0 shadow-none': wizardMode,
        '!mt-0 lg:w-2/3 xl:w-1/2': !wizardMode,
      }"
    >
      <section class="flex justify-between">
        <span class="relative z-10 w-full">
          <label
            for="display_name"
            class="mb-1 flex w-full items-center justify-between text-sm font-bold uppercase tracking-widest"
          >
            <font-awesome-icon :icon="['fal', 'display']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("display name") }}
            <button
              v-tooltip="$t('translate this value')"
              class="ml-auto rounded-full px-2 uppercase text-theme-500 hover:bg-theme-100"
              @click="translate = !translate"
            >
              {{ $i18n.locale }}
              <font-awesome-icon :icon="['fal', !translate ? 'language' : 'circle-xmark']" />
            </button>
          </label>
          <input
            type="text"
            :value="$display_name(item.display_name)"
            name="name"
            class="input box-border w-full rounded border-theme-500 p-2"
            @input="
              update_item({
                key: 'display_name',
                value: $event.target.value,
              })
            "
          />
          <transition name="fade">
            <div v-show="translate" class="flex w-full flex-wrap bg-gray-100">
              <template v-for="lang in item.display_name">
                <div v-if="lang.iso !== $i18n.locale" :key="lang.iso" class="w-full p-4">
                  <label
                    :for="`category_name_${lang.iso}`"
                    class="flex text-xs font-bold uppercase tracking-wide"
                  >
                    {{ $t("Name") }}
                    <span class="ml-auto text-theme-500">
                      <font-awesome-icon :icon="['fal', 'flag']" />
                      {{ lang.iso }}
                    </span>
                  </label>
                  <input
                    v-model="
                      item.display_name[
                        item.display_name.findIndex((name) => name.iso === lang.iso)
                      ].display_name
                    "
                    type="text"
                    :name="`category_name_${lang.iso}`"
                    class="input"
                  />
                </div>
              </template>
            </div>
          </transition>
          <div class="flex w-full justify-between">
            <small class="text-gray-500">
              {{ $t("original name") }}: <b>{{ item.name }}</b>
            </small>
            <!-- <small class="text-gray-500">
              {{ $t("system key") }}: <b>{{ item.system_key }}</b>
            </small> -->
          </div>
        </span>

        <!-- <span class="relative z-0 -ml-1 mr-2 w-1/2">
            <label
              for="system_key"
              class="mb-1 ml-2 flex items-center text-sm font-bold uppercase tracking-widest"
            >
              <font-awesome-icon :icon="['fal', 'server']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("system key") }}
            </label>

            <input
              v-model="item.system_key"
              type="text"
              name="system_key"
              class="input w-full rounded-none rounded-r p-2 pl-4 hover:bg-gray-100"
            />
          </span> -->
      </section>

      <section class="mt-4 grid w-full grid-cols-2 gap-2">
        <div class="max-w-xs">
          <label
            for="media"
            class="mb-1 flex items-center text-sm font-bold uppercase tracking-widest"
          >
            <font-awesome-icon :icon="['fal', 'image']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("category image") }}
          </label>
          <UIMultiImageSelector
            class="rounded"
            disk="assets"
            :selected-image="item.media"
            :update-custom="update_item"
            @on-image-select="
              update_item({
                key: 'media',
                value: $event,
              })
            "
            @on-image-remove="
              update_item({
                key: 'media',
                value: '',
              })
            "
          />
        </div>

        <div>
          <span class="relative w-full flex-shrink-0 md:ml-2 md:w-1/2 lg:ml-0 lg:mt-4 lg:w-full">
            <label for="name" class="text-sm font-bold uppercase tracking-widest">
              {{ $t("description") }}
            </label>
            <textarea
              v-model="item.description"
              name="description"
              rows="4"
              class="input box-border w-full p-2"
            />
          </span>
        </div>
      </section>

      <section class="my-4 flex flex-wrap justify-around justify-self-center lg:w-1/2">
        <div
          class="relative mt-4 flex w-full items-center pb-2 capitalize sm:w-1/2 md:w-1/2 lg:w-full"
        >
          <font-awesome-icon :icon="['fal', 'heart-rate']" class="fa-fw mr-2 text-theme-500" />
          {{ $t("published") }}
          <div
            class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
            :class="[item.published ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="published"
              class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
              :class="[
                item.published ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
              ]"
            />
            <input
              id="published"
              v-model="item.published"
              type="checkbox"
              name="published"
              class="h-full w-full appearance-none focus:outline-none active:outline-none"
            />
          </div>
          <font-awesome-icon
            v-tooltip="
              // prettier-ignore
              $t('This will publish your category and make it available for use in your webshop')
            "
            :icon="['fal', 'circle-info']"
            class="fa-fw ml-auto mr-2 text-theme-500"
          />
        </div>

        <div
          v-if="producer"
          class="relative mt-2 flex w-full items-center sm:w-1/3 md:w-1/2 lg:w-full"
        >
          <font-awesome-icon :icon="['fal', 'radar']" class="fa-fw mr-2 text-theme-500" />
          {{ $t("shared in finder") }}
          <div
            class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
            :class="[item.shareable ? 'bg-theme-400' : 'bg-gray-300']"
          >
            <label
              for="shareable"
              class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
              :class="[
                item.shareable ? 'translate-x-6 border-theme-500' : 'translate-x-0 border-gray-300',
              ]"
            />
            <input
              id="shareable"
              v-model="item.shareable"
              type="checkbox"
              name="shareable"
              class="h-full w-full appearance-none focus:outline-none active:outline-none"
            />
          </div>
          <font-awesome-icon
            v-tooltip="$t('This will share your category in the product-finder in the Marketplace')"
            :icon="['fal', 'circle-info']"
            class="fa-fw ml-auto mr-2 text-theme-500"
          />
        </div>

        <div
          v-else
          class="mt-2 flex w-full flex-wrap items-center text-gray-500 sm:w-1/3 md:w-1/2 lg:w-full"
        >
          <font-awesome-icon :icon="['fal', 'radar']" class="fa-fw mr-2 text-gray-500" />
          {{ $t("shared in finder") }}
          <font-awesome-icon
            v-tooltip="$t('You need to be a producer to share your category in the product-finder')"
            :icon="['fal', 'circle-info']"
            class="fa-fw ml-auto mr-2 text-theme-500"
          />
          <NuxtLink
            v-if="!producer"
            to="/manage/tenant-settings/producer-information"
            class="hover:from-theme500 my-2 flex w-full items-center justify-center rounded-full bg-gradient-to-r from-theme-400 to-pink-500 px-4 py-2 text-sm text-white backdrop-opacity-80 transition-all hover:to-pink-600"
          >
            <font-awesome-icon :icon="['fal', 'industry-windows']" class="fa-fw mr-2" />
            {{ $t("I want to be a producer in the Marketplace") }}
          </NuxtLink>
        </div>
      </section>

      <div class="mb-4 border-t pt-4">
        <font-awesome-icon
          :icon="['fal', 'hand-holding-magic']"
          class="fa-fw mr-2 text-theme-500"
        />
        {{ $t("created on") }}
        <span class="ml-4 font-mono text-gray-500">
          <font-awesome-icon :icon="['fal', 'calendar']" class="fa-fw" />
          {{ moment(item.created_at).format("DD-MM-YY") }}
        </span>
        <span class="ml-4 font-mono text-gray-500">
          <font-awesome-icon :icon="['fal', 'clock']" class="fa-fw" />
          {{ moment(item.created_at).format("hh:mm:ss") }}
        </span>
      </div>

      <div class="mx-auto mt-4 flex w-full items-center justify-end" v-if="!wizardMode">
        <UIButton variant="success" class="px-4 py-1 !text-base" @click="saveItem()">
          <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
          {{ $t("save info") }}
        </UIButton>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";

export default {
  emits: ["update:item"],
  props: {
    item: { type: Object, required: true },
    type: {
      type: String,
      required: true,
    },
    producer: {
      type: Boolean,
      default: false,
    },
    wizardMode: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    return { api, handleError, handleSuccess };
  },
  data() {
    return {
      moment: moment,
      linked: {},
      media: this.item.media && this.item.media.length > 0 ? this.item.media : [],
      translate: false,
    };
  },
  methods: {
    update_item(value) {
      if (value.key == "media") {
        if (typeof value.value === "string" && value.value !== "") {
          this.item.media.push(value.value);
        } else {
          this.item.media.splice(value.value, 1);
        }
      }
      if (value.key === "display_name") {
        const localeEntry = this.item[value.key].find((name) => name.iso === this.$i18n.locale);
        if (localeEntry) {
          localeEntry.display_name = value.value;
        } else {
          console.error(`Locale entry not found for ${this.$i18n.locale}`);
        }
      }
      // --- Emit updated item to parent (CategoryConfigurationStep) ---
      this.$emit("update:item", { ...this.item });
    },
    saveItem() {
      // const object = {
      //   name: this.item.name,
      //   display_name: this.item.display_name,
      //   system_key: this.item.system_key,
      //   description: this.item.description,
      //   published: this.item.published,
      //   shareable: this.item.shareable,
      //   media: this.item.media,
      // };

      this.api
        .put(`categories/${this.item.slug}`, this.item)
        .then((response) => {
          this.handleSuccess(response);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
    getLinked(ref_supplier, slug) {
      this.api.get(`suppliers/${ref_supplier}/categories/${slug}`).then((response) => {
        this.linked = response;
      });
    },
  },
};
</script>

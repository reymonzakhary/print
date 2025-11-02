<template>
  <div>
    <div class="mx-auto lg:w-2/3 xl:w-1/2" v-if="!wizardMode">
      <h2 class="w-full text-lg font-bold tracking-wide">
        <span class="icon">
          <font-awesome-icon :icon="['fal', 'gears']" fixed-width />
        </span>
        {{ $t("Category configuration") }}
      </h2>
      <p class="mb-4 text-sm text-gray-500">
        <span class="italic">{{
          // prettier-ignore
          $t("Additional configuration for your category, like VAT and bleed")
        }}</span>
      </p>
    </div>

    <main
      class="mx-auto w-full rounded-md bg-white p-4 shadow-md dark:bg-gray-700"
      :class="{
        '!mt-4 w-full rounded-none !p-0 shadow-none': wizardMode,
        '!mt-0 lg:w-2/3 xl:w-1/2': !wizardMode,
      }"
    >
      <section class="flex gap-4">
        <article class="w-1/2">
          <h2 class="mb-4 w-full font-thin uppercase tracking-wide" v-if="!wizardMode">
            {{ $t("general") }}
          </h2>
          <article>
            <label
              for="categoryVAT"
              class="flex items-center text-sm font-bold uppercase tracking-widest"
            >
              <font-awesome-icon :icon="['fal', 'border-outer']" class="fa-fw mr-1 text-gray-500" />
              {{ $t("vat") }}

              <VMenu theme="tooltip" class="ml-2">
                <font-awesome-icon :icon="['fal', 'circle-info']" class="fa-fw text-theme-500" />

                <template #popper>
                  <div class="flex max-w-80 flex-col p-4">
                    {{
                      // prettier-ignore
                      $t("This VAT is used when calculating the price of products in this category")
                    }}
                    <br />
                    <br />
                    {{
                      // prettier-ignore
                      $t("Leave empty to use system VAT or fill to overide the default system VAT")
                    }}
                  </div>
                </template>
              </VMenu>
            </label>

            <section class="mb-8 flex items-center">
              <UIInputText
                v-model="item.vat"
                type="number"
                affix="%"
                step="1"
                :placeholder="$t('use VAT from system')"
                :value="item.vat"
                name="categoryVAT"
              />
            </section>
            <section>
              <label
                for="display_name"
                class="flex items-center text-sm font-bold uppercase tracking-widest"
              >
                <font-awesome-icon
                  :icon="['fal', 'border-outer']"
                  class="fa-fw mr-1 text-gray-500"
                />
                {{ $t("bleed") }}

                <VMenu theme="tooltip" class="ml-2">
                  <font-awesome-icon :icon="['fal', 'circle-info']" class="fa-fw text-theme-500" />

                  <template #popper>
                    <div class="flex max-w-80 flex-col p-4">
                      {{
                        // prettier-ignore
                        $t('This bleed is used when calculating the price of products in this category')
                      }}
                    </div>
                  </template>
                </VMenu>
              </label>

              <UIInputText
                v-model="item.bleed"
                :prefix="`${$t('bleed')}`"
                :affix="`${$t('mm')}`"
                step="1"
                :placeholder="$t('bleed')"
                :value="item.bleed"
                type="number"
                name="categoryBleed"
              />
            </section>
          </article>
        </article>
        <!-- <article class="w-1/2">
          <h2 class="w-full mb-4 font-thin tracking-wide uppercase">{{ $t("preflight") }}</h2>
          <section class="space-y-4">
            <section>
              <label for="display_name" class="text-sm font-bold tracking-widest uppercase">
                <font-awesome-icon
                  :icon="['fal', 'standard-definition']"
                  class="mr-1 text-gray-500 fa-fw"
                />
                {{ $t("min. resolution") }}
              </label>
              <UIInputText
                v-model="item.configuration.minResolution"
                :affix="`${$t('dpi')}`"
                step="1"
                :placeholder="70"
                :value="item.configuration?.minResolution ?? 70"
                type="number"
                name="minResolution"
              ></UIInputText>
            </section>
            <section>
              <label for="display_name" class="text-sm font-bold tracking-widest uppercase">
                <font-awesome-icon
                  :icon="['fal', 'high-definition']"
                  class="mr-1 text-gray-500 fa-fw"
                />
                {{ $t("max. resolution") }}
              </label>
              <UIInputText
                v-model="item.configuration.maxResolution"
                :affix="`${$t('dpi')}`"
                step="1"
                :placeholder="300"
                :value="item.configuration?.maxResolution ?? 300"
                type="number"
                name="maxResolution"
              ></UIInputText>
            </section>
            <section>
              <label for="display_name" class="text-sm font-bold tracking-widest uppercase">
                <font-awesome-icon :icon="['fal', 'files']" class="mr-1 text-gray-500 fa-fw" />
                {{ $t("number of pages") }}
              </label>
              <div class="flex items-center justify-between my-1">
                <div class="italic text-gray-500">{{ $t("box") }}</div>
                <UISwitch
                  key="pagesMode"
                  name="pagesMode"
                  variant="default"
                  :value="pagesMode"
                  @input="pagesMode = !pagesMode"
                ></UISwitch>
                <div class="italic text-gray-500">{{ $t("number of pages") }}</div>
              </div>
              <div v-if="!pagesMode">
                <UISelector :options="filteredBoxes" display-property="display_name"></UISelector>
              </div>
              <div v-else>
                <UIInputText
                  v-model="item.configuration.pages"
                  :affix="`${$t('pages')}`"
                  step="1"
                  :placeholder="1"
                  :value="item.configuration?.pages ?? 1"
                  type="number"
                  name="pages"
                ></UIInputText>
              </div>
            </section>
          </section>
        </article> -->
      </section>
      <section class="mt-2 flex justify-end" v-if="!wizardMode">
        <UIButton
          variant="success"
          class="px-4 py-1 !text-base"
          @click="$emit('onUpdateConfig', item.bleed)"
        >
          <font-awesome-icon :icon="['fal', 'floppy-disk']" class="mr-2" />
          {{ $t("save configuration") }}
        </UIButton>
      </section>
    </main>
  </div>
</template>

<script>
export default {
  name: "CategoryConfig",
  props: {
    item: { type: Object, required: true },
    wizardMode: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onUpdateConfig"],
  setup() {
    const { handleError, handleSuccess } = useMessageHandler();
    const api = useAPI();
    return {
      api,
      handleError,
      handleSuccess,
    };
  },
  data() {
    return {
      pagesMode: false,
      boxes: [],
      // item: {
      //   bleed: null,
      //   configuration: {
      //     minResolution: 70,
      //     maxResolution: 300,
      //     pages: 1,
      //   },
      //   vat: null,
      // },
    };
  },
  computed: {
    filteredBoxes() {
      return this.boxes.filter((box) => box.calc_ref);
    },
  },
  mounted() {
    this.api
      .get("boxes?per_page=999999")
      .then((response) => {
        this.boxes = response.data;
      })
      .catch((error) => {
        this.handleError(error);
      });
  },
};
</script>

<style scoped>
/* Your component-specific styles go here */
</style>

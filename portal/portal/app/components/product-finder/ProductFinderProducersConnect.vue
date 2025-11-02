<template>
  <div class="mt-6 p-6">
    <div class="mb-6 w-full text-center">
      <DynamicZeroState
        :message="$t('No price responses') + '...'"
        :icon="['fad', 'money-check-dollar']"
      />

      <UIButton
        @click="$emit('close')"
        class="mx-2 !px-4 !text-base"
        variant="theme"
        icon-placement="left"
        :icon="['fal', 'turn-left']"
      >
        {{ $t("Back to productfilters") }}
      </UIButton>

      <h3 class="mb-4 mt-8 text-xl font-semibold text-gray-900 dark:text-gray-100">
        {{ $t("Consider connecting with more producers in this category.") }}
      </h3>
      <p class="text-sm italic text-gray-500">
        {{
          $t("Connecting with these producers will help you get competitive pricing for your needs")
        }}
      </p>
    </div>

    <section
      v-if="producers.length > 0"
      class="mx-auto grid w-full max-w-4xl grid-cols-1 justify-center gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
      <div v-for="producer in producers" :key="producer.id">
        <transition-group name="fade">
          <button
            class="group relative w-full overflow-hidden rounded-md p-4 shadow transition-shadow duration-150"
            :class="{
              'cursor-not-allowed bg-gray-200 shadow-none dark:bg-gray-900': producer.contract,
              'cursor-pointer bg-white shadow-md hover:shadow-xl dark:bg-gray-700 dark:shadow-black/45':
                !producer.contract,
              'lg:col-start-2': producers.filter((p) => p.contract).length < 2,
            }"
            :disabled="producer.contract || producer.is_me"
            type="button"
            @click="
              !producer.contract || !producer.is_me
                ? $router.push(
                    '/marketplace/producer-finder/' +
                      producer.company_name +
                      '?id=' +
                      producer.website_id,
                  )
                : null
            "
          >
            <div class="mt-8" :class="{ 'pb-16': producer.is_me }">
              <HandshakeStatus :producer="producer" position="top" />

              <div class="leading-3">
                <small v-if="producer.is_me" class="mb-8 text-gray-600 dark:text-gray-400">
                  {{ $t("You also have this category!") }}
                </small>
                <small
                  v-if="producer.contract?.st === 320"
                  class="mb-8 text-gray-600 dark:text-gray-400"
                >
                  {{ $t("Already connected, select another product variation for pricing.") }}
                </small>
                <small v-if="!producer.contract" class="mb-8 text-gray-600 dark:text-gray-400">
                  {{ $t("You can connect to this supplier for") }} {{ category.name }}
                </small>
                <!-- Show handshake icon if not connected -->
              </div>

              <figure class="mb-2 flex w-full items-center justify-center p-2">
                <img
                  :src="producer.logo"
                  :alt="producer.tenant_name"
                  class="max-h-24 object-contain"
                />
              </figure>

              <h3 class="font-bold">
                {{ producer.company_name ?? producer.tenant_name }}
              </h3>
              <div
                class="overflow-hidden text-ellipsis text-nowrap text-sm text-gray-600 group-hover:text-wrap dark:text-gray-400"
              >
                {{ producer.page_title }}
              </div>
            </div>

            <HandshakeIcon v-if="!producer.is_me" :producer="producer" class="mt-4" />
          </button>
        </transition-group>
      </div>
    </section>

    <ProducerZeroState :message="$t('No producers available for this category')" v-else />

    <div v-if="producers.length > 3" class="mt-6 text-center">
      <UIButton
        @click="$emit('close')"
        class="mx-2 !px-4 !text-base"
        variant="theme"
        icon-placement="left"
        :icon="['fal', 'turn-left']"
      >
        {{ $t("Back to productfilters") }}
      </UIButton>
    </div>
  </div>
</template>

<script setup>
const { t: $t } = useI18n();

const props = defineProps({
  category: {
    type: Object,
    required: true,
  },
  producers: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close"]);
</script>

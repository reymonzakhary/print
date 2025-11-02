<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      <span class="capitalize">{{ $t("remove") }} {{ item.label }}</span>
    </template>

    <template #modal-body>
      <section class="flex max-w-lg flex-wrap">
        <div class="max-h-screen p-2" style="min-width: 400px">
          {{ $t("will remove") }}
          <b>{{ item.label }}</b
          >. {{ $t("are you sure") }}

          <!-- <div
						class="p-2 mt-2 bg-gray-100 rounded dark:bg-gray-800"
						v-if="item.resources.length > 0"
					>
						<h2 class="text-sm font-bold tracking-wide uppercase">
							<font-awesome-icon
								:icon="['fad', 'triangle-exclamation']"
								class="text-base text-orange-500"
							/>
							{{ $t("page contains the following resources") }}
						</h2>
						<h3 class="mt-2 ml-6 text-xs font-bold tracking-wide uppercase">
							{{ $t("resources") }}:
						</h3> -->
          <!-- <ul class="ml-6 divide-y">
							<li v-for="resource in item.resources">
								{{ resource.title }}
							</li>
						</ul> -->
        </div>
      </section>
    </template>

    <template #confirm-button>
      <button
        class="mr-2 rounded-full bg-red-500 px-5 py-1 text-sm text-white transition-colors hover:bg-red-700"
        @click="(delete_production_day(item.slug), closeModal())"
      >
        {{ $t("remove") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapActions } from "vuex";
import moment from "moment";

export default {
  name: "ProductionDaysRemoveItem",
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      moment: moment,
    };
  },
  methods: {
    ...mapActions({
      get_production_days: "production_days/get_production_days",
      delete_production_day: "production_days/delete_production_day",
    }),
    closeModal() {
      this.$parent.showRemoveItem = false;
    },
  },
};
</script>

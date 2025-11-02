<template>
  <li class="relative">
    <div
      class="cursor-pointer bg-white text-sm shadow-md shadow-gray-300 transition-colors duration-75 dark:bg-gray-700 dark:shadow-black"
      :class="{
        'rounded-t': wideView,
        'border-b': !wideView,
      }"
    >
      <section class="relative grid grid-cols-7 items-center px-4 pt-4">
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("supplier") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("art. nr") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("material") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("weight") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("EAN") }}
        </div>
        <div
          class="col-span-2 flex-1 items-center justify-end text-right text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("actions") }}
        </div>
      </section>

      <section class="relative grid grid-cols-7 items-center px-4 pb-4">
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.supplier"
            name="supplier"
            :placeholder="$t('supplier')"
          />
          <div v-else>{{ paper.supplier }}</div>
        </div>

        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.art_nr"
            name="art_nr"
            :placeholder="$t('art. nr')"
          />
          <div v-else>{{ paper.art_nr }}</div>
        </div>

        <div class="flex-1 text-ellipsis whitespace-nowrap">
          <v-select
            v-if="editablePaper"
            :model-value="paper.material"
            :options="materials"
            label="name"
            class="input material-select-scroll z-50 rounded bg-white !p-1 !py-0 text-sm text-theme-900"
            @option:selected="setMaterial($event)"
          >
            <template #option="material">
              {{ $display_name(material.display_name) }} ( {{ material.system_key }} )
            </template>
          </v-select>
          <div
            v-else
            v-tooltip="paper.material"
            class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap"
          >
            <b>{{ paper.material }}</b>
          </div>
        </div>

        <div class="flex-1 text-ellipsis whitespace-nowrap pr-2">
          <v-select
            v-if="editablePaper"
            :model-value="paper.grs"
            :options="grs"
            label="name"
            class="input material-select-scroll z-50 rounded bg-white !p-1 !py-0 text-sm text-theme-900"
            @option:selected="setGrs($event)"
          >
            <template #option="gr">
              <span
                v-if="
                  catalogue.some((p) => {
                    const match = p.material === paper.material && p.grs_id === gr.id;

                    return match;
                  })
                "
                class="font-semibold text-green-600"
                >✓</span
              >

              {{ $display_name(gr.display_name) }} ( {{ gr.system_key }} )
            </template>
          </v-select>
          <div v-else class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
            <b>{{ $display_name(grs.find((g) => g.id === paper.grs_id)?.display_name) }}</b>
            <small>/{{ $t("m2") }}</small>
          </div>
          <!-- {{ grs }} -->
        </div>

        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.ean"
            name="paperEan"
            class="ml-2 !border-amber-500"
            :placeholder="$t('EAN')"
          />
          <div v-else>{{ paper.ean }}</div>
        </div>

        <div class="col-span-2 flex flex-1 items-center justify-end space-x-1 pl-2">
          <UIButton
            v-if="!editablePaper && (newPaper === null || newPaper === 0) && editable === null"
            variant="default"
            default="Button"
            :icon="['fas', 'pencil']"
            :disabled="false"
            @click="$emit('onEdit', index)"
          />

          <UIButton
            v-if="!editablePaper && (newPaper === null || newPaper === 0) && editable === null"
            variant="default"
            default="Button"
            :icon="['fas', 'copy']"
            :disabled="false"
            @click="$emit('onCopy', { ...paper })"
          />
          <UIButton
            v-if="editablePaper && newPaper === null"
            variant="success"
            default="Button"
            :icon="['fas', 'check']"
            :disabled="false"
            :title="$t('save')"
            @click="$emit('onSave', paper)"
          >
            {{ $t("save") }}
          </UIButton>
          <UIButton
            v-if="editablePaper && newPaper === index"
            variant="success"
            default="Button"
            :icon="['fas', 'check']"
            :disabled="false"
            :title="$t('add')"
            @click="$emit('onAdd', paper)"
          >
            {{ $t("save new paper") }}
          </UIButton>
          <UIButton
            v-if="editablePaper && newPaper === index"
            variant="neutral"
            default="Button"
            :icon="['fas', 'xmark']"
            :disabled="false"
            :title="$t('cancel')"
            @click="$emit('onCancel', paper)"
          />
          <UIButton
            v-if="editablePaper && newPaper === null"
            variant="neutral"
            default="Button"
            :icon="['fas', 'xmark']"
            :disabled="false"
            :title="$t('cancel')"
            @click="$emit('onCancelEdit', paper)"
          />
          <UIButton
            v-if="!newPaper && paper.id"
            :icon="['fas', 'trash']"
            variant="danger"
            :disabled="false"
            default="Button"
            :title="$t('delete')"
            @click="$emit('onDelete', { paper, index })"
          />
        </div>
      </section>
    </div>

    <div
      v-if="wideView || editablePaper"
      class="relative z-10 rounded-b border-t bg-white p-4 shadow-md shadow-gray-300 dark:bg-gray-900 dark:shadow-black"
      :class="{
        'rounded-b': wideView,
        '': !wideView,
      }"
    >
      <section class="relative grid grid-cols-7 items-center">
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("roll / sheet") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("width") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("height") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("length") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("density") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("price") }}
        </div>
        <div
          class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap text-xs font-semibold uppercase text-gray-400"
        >
          {{ $t("per") }}
        </div>
      </section>
      <section class="relative grid grid-cols-7 items-center">
        <div class="flex flex-1 items-center overflow-hidden text-ellipsis whitespace-nowrap">
          {{ $t("roll") }}
          <UISwitch
            :key="`paper_sheet_${index}`"
            class="mx-2"
            :value="paper.sheet"
            :disabled="!editablePaper"
            variant="default"
            :name="`paper_sheet_${index}`"
            @input="paper.sheet = $event"
          />
          {{ $t("sheet") }}
        </div>
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.width"
            type="number"
            name="paperWidth"
            :affix="$t('mm')"
            placeholder="0"
          />
          <div v-else>
            {{ paper.width }} <small>{{ $t("mm") }}</small>
          </div>
        </div>
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.height"
            type="number"
            name="paperHeight"
            :affix="$t('mm')"
            placeholder="0"
          />
          <div v-else>
            {{ paper.height }} <small>{{ $t("mm") }}</small>
          </div>
        </div>
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper && !paper.sheet"
            v-model="paper.length"
            type="number"
            name="paperLength"
            :affix="$t('mm')"
            placeholder="0"
          />
          <div v-else>
            {{ paper.length }} <small>{{ $t("mm") }}</small>
          </div>
        </div>
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UIInputText
            v-if="editablePaper"
            v-model="paper.density"
            type="number"
            name="paperDensity"
            :affix="$t('grs/m3')"
            step="0.01"
            placeholder="0"
          />
          <div v-else>
            {{ paper.density }} <small>{{ $t("g/cm3") }}</small>
          </div>
        </div>
        <div class="flex-1 overflow-hidden text-ellipsis whitespace-nowrap">
          <UICurrencyInput
            v-if="editablePaper"
            v-model="paper.price"
            input-class="w-full !p-1 border-green-500 ring-green-200 focus:border-green-500"
            name="price"
            :options="{
              precision: 5,
              accountingSign: false,
            }"
          />
          <div v-else>{{ paper.display_price }}</div>
        </div>
        <div class="flex-1 text-ellipsis whitespace-nowrap pr-2">
          <v-select
            v-if="editablePaper"
            v-model="paper.calc_type"
            :options="calcTypes"
            :reduce="(option) => option.value"
            label="name"
            class="input z-50 rounded bg-white !p-1 !py-0 text-sm text-theme-900"
          />
          <div v-else class="flex w-full items-end overflow-hidden text-ellipsis whitespace-nowrap">
            <small>{{ $t("per") }}</small>
            <div v-for="(type, index) in calcTypes" :key="index">
              <span v-if="paper.calc_type === type.value" class="ml-1">
                {{ type.name }}
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    paper: {
      type: Object,
      required: true,
    },
    materials: {
      type: Array,
      required: true,
    },
    grs: {
      type: Array,
      required: true,
    },
    catalogue: {
      type: Array,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    editable: {
      type: [Number, null],
      required: true,
    },
    newPaper: {
      type: [Number, null],
      required: true,
    },
    wideView: {
      type: Boolean,
      required: false,
      default: true,
    },
  },
  emits: ["onCopy", "onAdd", "onSave", "onDelete", "onEdit", "onCancel", "onCancelEdit"],
  data() {
    return {
      // newPaper: false,
      selected: null,
      showDetails: false,
      calcTypes: [
        { value: "kg", name: this.$t("kg") },
        { value: "sheet", name: this.$t("sheet") },
        { value: "sqm", name: this.$t("m2") },
        { value: "lm", name: this.$t("m1") },
      ],
    };
  },
  computed: {
    editablePaper() {
      if (this.editable === this.index) {
        return true;
      } else {
        return false;
      }
    },
  },
  methods: {
    setMaterial(value) {
      if (!value) return;
      this.paper.material = value.name;
      this.paper.material_link = value.linked;
      this.paper.material_id = value.id;
    },
    setGrs(value) {
      if (!value) return;
      this.paper.grs = value.name;
      this.paper.grs_link = value?.linked;
      this.paper.grs_id = value.id;
    },
  },
};
</script>

<style scoped>
/* Allow horizontal scroll */
.material-select-scroll :deep(.vs__dropdown-menu) {
  overflow-y: auto;
  min-width: max-content;
}

/* Make option stretch to full content width, not just visible width */
.material-select-scroll :deep(.vs__dropdown-option) {
  overflow-x: visible;
  /* white-space: normal; */
  width: 100%; /* At least as wide as dropdown */
  display: block;
}

/* Style the scrollbar */
.material-select-scroll :deep(.vs__dropdown-menu::-webkit-scrollbar) {
  height: 6px;
  width: 6px;
}
</style>

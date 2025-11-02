<template>
  <article>
    <div class="rounded-md border border-orange-500 bg-white p-4 shadow-md dark:bg-gray-700">
      <div
        class="-m-4 mb-4 rounded-t bg-orange-100 p-2 text-center font-normal normal-case text-orange-500 shadow-orange-200 dark:bg-orange-900 dark:shadow-orange-900"
      >
        <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-2" />
        <b>{{ $t("WARNING") }}!</b> <br />
        <span class="text-sm">
          {{ $t("These fields are edited for every category that uses this option") }}.
        </span>
      </div>

      <section class="flex flex-wrap justify-between pt-2">
        <div class="relative z-10 w-full">
          <label for="display_name" class="text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'display']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("display name") }}
            <button
              v-tooltip="$t('translate this value')"
              class="rounded-full px-2 text-sm uppercase text-theme-500 hover:bg-theme-100"
              @click="translate = !translate"
            >
              {{ $i18n.locale }}
              <font-awesome-icon :icon="['fal', !translate ? 'language' : 'circle-xmark']" />
            </button>
          </label>
          <input
            v-model="
              item.display_name[item.display_name.findIndex((name) => name.iso === $i18n.locale)]
                .display_name
            "
            type="text"
            name="name"
            class="input box-border w-full rounded border-theme-500 p-2"
            @input="
              update_item({
                key: 'display_name',
                value: item.display_name,
              })
            "
          />
          <transition name="fade">
            <div v-show="translate" class="flex w-full flex-wrap bg-gray-100">
              <template v-for="lang in item.display_name">
                <div v-if="lang.iso !== $i18n.locale" :key="lang.iso" class="p-4">
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
                    @input="update_item({ key: 'display_name', value: item.display_name })"
                  />
                </div>
              </template>
            </div>
          </transition>
          <small class="text-gray-500">
            {{ $t("original name") }}: <b>{{ item.name }}</b>
          </small>
        </div>
        <!-- <span class="relative z-0 -ml-1 mr-2 w-1/2 flex-1">
          <label for="system_key" class="ml-2 text-sm font-bold uppercase tracking-widest">
            <font-awesome-icon :icon="['fal', 'server']" class="fa-fw mr-2 text-gray-500" />
            {{ $t("system key") }}
          </label>
          <input
            type="text"
            :value="item.system_key"
            name="system_key"
            class="input w-full rounded-none rounded-r p-2 pl-4"
            @input="update_item({ key: 'system_key', value: $event.target.value })"
          />
        </span> -->
        <div class="mt-4 flex gap-2">
          <div class="w-1/2">
            <label for="images" class="text-sm font-bold uppercase tracking-widest">
              {{ $t("images") }}
            </label>
            <UIMultiImageSelector
              disk="assets"
              :selected-image="item.media"
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
          <div class="w-1/2">
            <span class="relative w-full flex-shrink-0 md:ml-2 md:w-1/2 lg:ml-0 lg:mt-4 lg:w-full">
              <label for="description" class="text-sm font-bold uppercase tracking-widest">
                {{ $t("description") }}
              </label>
              <textarea
                :value="item.description"
                name="description"
                class="input w-full p-2"
                rows="3"
                @change="
                  update_item({
                    key: 'description',
                    value: $event.target.value,
                  })
                "
              />
            </span>
          </div>
        </div>
        <!-- ADDITIONAL -->
        <section class="relative mt-8 w-full rounded bg-gray-100 p-2 dark:bg-gray-800">
          <div class="flex">
            <label for="additional" class="mr-auto text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon :icon="['fal', 'trailer']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("additional") }}
            </label>
          </div>
          <div class="my-2">
            <label class="mr-auto text-sm font-bold uppercase tracking-widest">
              {{ $t("reference type") }}
            </label>
            <UISelector
              :value="calcRefType"
              :options="[
                { value: 'none', label: $t('None') },
                { value: 'main', label: $t('Main reference') },
                { value: 'binding_type', label: $t('Binding Type') },
                { value: 'binding_direction', label: $t('Binding Direction') },
              ]"
              @input="
                setCalcRefType($event);
                update_item({
                  key: 'additional',
                  value: { ...item.additional, calc_ref_type: $event, calc_ref: null },
                });
              "
            />
          </div>
          <div v-if="calcRefType === 'binding_type'" class="my-2">
            <label for="binding_type" class="mr-auto text-sm font-bold uppercase tracking-widest">
              {{ $t("binding type") }}
            </label>
            <UISelector
              :value="item.additional?.calc_ref"
              :options="[
                { value: 'saddle_stitch', label: $t('Saddle Stitch') },
                { value: 'perfect_bound', label: $t('Perfect Bound') },
                { value: 'case_bound', label: $t('Case Bound') },
                { value: 'spiral_bound', label: $t('Spiral Bound') },
                { value: 'wire_o', label: $t('Wire-O') },
                { value: 'comb_bound', label: $t('Comb Bound') },
                { value: 'section_sewn', label: $t('Section Sewn') },
                { value: 'lay_flat', label: $t('Lay Flat') },
                { value: 'thermal_binding', label: $t('Thermal Binding') },
                { value: 'tape_binding', label: $t('Tape Binding') },
                { value: 'coptic_stitch', label: $t('Coptic Stitch') },
                { value: 'stab_binding', label: $t('Stab Binding') },
                { value: 'pamphlet', label: $t('Pamphlet') },
                { value: 'accordion_fold', label: $t('Accordion Fold') },
              ]"
              @input="
                update_item({
                  key: 'additional',
                  value: { ...item.additional, calc_ref: $event },
                })
              "
            />
          </div>
          <div v-if="calcRefType === 'binding_direction'" class="my-2">
            <label
              for="binding_direction"
              class="mr-auto text-sm font-bold uppercase tracking-widest"
            >
              {{ $t("binding direction") }}
            </label>
            <UISelector
              :value="item.additional?.calc_ref"
              :options="[
                { value: 'left', label: $t('binding on left side') },
                { value: 'top', label: $t('binding on top') },
              ]"
              @input="
                update_item({
                  key: 'additional',
                  value: { ...item.additional, calc_ref: $event },
                })
              "
            />
          </div>
          <div v-if="calcRefType === 'main'" class="my-2">
            <label for="main" class="mr-auto text-sm font-bold uppercase tracking-widest">
              {{ $t("main reference") }}
            </label>
            <UISelector
              :value="item.additional?.calc_ref"
              :options="[
                { value: '', selected: item.additional?.calc_ref_type === none, label: $t('None') },
                { value: 'format', label: $t('Format') },
                { value: 'material', label: $t('Material') },
                { value: 'weight', label: $t('Weight') },
                { value: 'printing_colors', label: $t('Printing colors') },
              ]"
              @input="
                update_item({
                  key: 'additional',
                  value: { ...item.additional, calc_ref: $event },
                })
              "
            />
          </div>
        </section>
        <!-- ADDITIONAL end -->
      </section>
      <section class="mt-4 flex flex-wrap justify-around pb-4 dark:border-gray-900">
        <div
          class="relative mt-4 flex w-full items-center justify-between pb-2 capitalize sm:w-1/3 md:w-full"
        >
          <div>
            <font-awesome-icon :icon="['fal', 'heart-rate']" class="fa-fw mr-2 text-theme-500" />
            {{ $t("published") }}
          </div>
          <div class="flex items-center">
            <div
              class="relative mx-2 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
              :class="[item.published ? 'bg-theme-400' : 'bg-gray-300']"
            >
              <label
                for="published"
                class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear dark:bg-gray-700"
                :class="[
                  item.published
                    ? 'translate-x-6 border-theme-500'
                    : 'translate-x-0 border-gray-300',
                ]"
              />
              <input
                id="published"
                type="checkbox"
                name="published"
                class="h-full w-full appearance-none focus:outline-none active:outline-none"
                :value="item.published"
                @input="
                  update_item({
                    key: 'published',
                    value: $event.target.checked,
                  })
                "
              />
            </div>
            <font-awesome-icon
              v-tooltip="
                $t('This item will be available if your category is published on your webshop')
              "
              :icon="['fal', 'circle-info']"
              class="fa-fw ml-auto mr-2 text-theme-500"
            />
          </div>
        </div>
        <!-- TODO: re-enable when backend can handle this in the productfinder
          <div v-if="producer" class="relative mt-2 flex w-full items-center sm:w-1/3 md:w-full">
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
          class="mt-2 flex w-full flex-wrap items-center text-gray-500 sm:w-1/3 md:w-full"
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
            class="mx-auto my-2 flex w-auto items-center justify-center rounded-full bg-gradient-to-r from-theme-400 to-pink-500 px-4 py-2 text-sm text-white backdrop-opacity-80 transition-all hover:from-theme-500 hover:to-pink-600"
          >
            <font-awesome-icon :icon="['fal', 'industry-windows']" class="fa-fw mr-2" />
            {{ $t("I want to be a producer in the Marketplace") }}
          </NuxtLink>
        </div> -->
      </section>
    </div>

    <div class="mt-4 rounded-md border border-theme-500 bg-white p-4 shadow-md dark:bg-gray-700">
      <div
        class="-m-4 mb-4 rounded-t bg-theme-100 p-2 text-center font-normal normal-case text-theme-500 shadow-theme-200 dark:bg-theme-900 dark:shadow-theme-900"
      >
        <font-awesome-icon :icon="['fad', 'triangle-exclamation']" class="mr-2" />
        <span class="text-sm">
          {{ $t("These fields are only edited for") }}
          <strong>{{ $display_name(selected_category.display_name) }}</strong
          >.</span
        >
      </div>
      <section v-if="configEditable" class="flex">
        <article class="mr-2 w-1/2">
          <section class="mb-2 mb-4 block border-b pb-4">
            <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon
                :icon="['fal', 'hourglass-start']"
                class="fa-fw mr-2 text-gray-500"
              />
              {{ $t("start cost") }}
            </label>
            <span class="relative">
              <UICurrencyInput
                :model-value="item.start_cost"
                :options="{
                  precision: 5,
                }"
                input-class="w-full text-base border-green-500 ring-green-200 focus:border-green-500 dark:border-green-500"
                @update:model-value="
                  update_item({
                    key: 'start_cost',
                    value: $event,
                  })
                "
              />
            </span>
          </section>

          <!-- DYNAMIC -->
          <section class="relative mb-2 mt-2 rounded bg-gray-100 p-2 dark:bg-gray-800">
            <div class="flex">
              <label for="dynamic" class="mr-auto text-sm font-bold uppercase tracking-widest">
                <font-awesome-icon
                  :icon="['fal', 'atom-simple']"
                  class="fa-fw mr-2 text-gray-500"
                />
                {{ $t("Dynamic") }}
              </label>
              <UISwitch :value="isDynamic" name="dynamic" @input="setIsDynamic($event)" />
            </div>
            <div v-if="isDynamic" class="my-2">
              <label for="dynamic_type" class="mr-auto text-sm font-bold uppercase tracking-widest">
                <!-- <font-awesome-icon :icon="['fal', 'vacuum-robot']" class="mr-2 text-gray-500 fa-fw" /> -->
                {{ $t("type") }}
              </label>
              <UISelector
                :value="item.dynamic_type"
                :options="[
                  { value: 'pages', label: $t('pages') },
                  { value: 'format', label: $t('format') },
                  { value: 'sides', label: $t('sides') },
                ]"
                @input="
                  update_item({
                    key: 'dynamic_type',
                    value: $event,
                  })
                "
              />
            </div>
          </section>
          <!-- DYNAMIC end -->

          <!-- DIMENSION -->
          <section class="relative my-2 block">
            <label for="dimension" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon :icon="['fal', 'cube']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("Dimension") }}
            </label>
            <UISelector
              :options="[
                { value: '2d', label: '2D' },
                { value: '3d', label: '3D', disabled: true },
              ]"
              placeholder="Dimension"
              :value="item.dimension || '2d'"
              name="dimension"
              @input="setIs3D($event)"
            />
          </section>
          <!-- DIMENSION end -->
          <!-- TODO: input_type and parent are not yet used, disabled for interface simplicity -->
          <!-- <section class="block relative my-4">
            <label for="input_type" class="text-sm font-bold tracking-widest uppercase">
              <font-awesome-icon :icon="['fal', 'symbols']" class="mr-2 text-gray-500 fa-fw" />
              {{ $t("input type") }}
            </label>
            <select
              id="input_type"
              disabled
              :value="item.input_type"
              name="input_type"
              class="p-2 w-full input"
              @change="
                update_item({
                  key: 'input_type',
                  value: $event.target.value,
                })
              "
            >
              <option value="radio" :checked="item.input_type === 'radio'">Radio</option>
              <option value="checkbox" :checked="item.input_type === 'checkbox'">Checkbox</option>
              <option value="text" :checked="item.input_type === 'text'">Text</option>
              <option value="number" :checked="item.input_type === 'number'">Number</option>
              <option value="select" :checked="item.input_type === 'select'">Select</option>
            </select>
          </section> -->
          <!-- <section class="block relative my-4">
            <label for="input_type" class="text-sm font-bold tracking-widest uppercase">
              {{ $t("parent") }}
            </label>
            <div v-if="item.parent" class="flex">
              <input
                v-model="filter"
                class="p-2 w-full input"
                type="text"
                placeholder="Search all options"
              />
              <font-awesome-icon
                :icon="['fal', 'filter']"
                class="absolute right-0 mt-2 mr-4 text-gray-600"
              />
            </div>
            <div v-else>{{ item.parent }}</div>
            <div v-if="item.parent" class="rounded-b divide-y shadow-md">
              <div
                v-for="parent in parents"
                :key="parent.display_name"
                class="p-2"
                @click="
                  update_item({
                    key: 'parent',
                    value: parent,
                  })
                "
              >
                {{ $display_name(parent.display_name) }}
              </div>
            </div>
          </section> -->
        </article>
        <div class="ml-2 w-1/2">
          <section class="mb-4 block border-b pb-4">
            <label
              for="calculation_method"
              class="flex items-center text-sm font-bold uppercase tracking-widest"
            >
              <font-awesome-icon :icon="['fal', 'calculator']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("calculation method") }}
              <VMenu theme="tooltip" class="mb-1 ml-2">
                <font-awesome-icon :icon="['fal', 'circle-info']" class="fa-fw text-theme-500" />

                <template #popper>
                  <div class="flex max-w-80 flex-col p-4">
                    {{
                      // prettier-ignore
                      $t("This setting has effect on the runs you set on the right side. ")
                    }}
                    <br />
                    <br />
                    {{
                      // prettier-ignore
                      $t("If qty is selected, the price will be calculated by the quantity of items.")
                    }}
                    <br />
                    {{
                      // prettier-ignore
                      $t("If sqm is selected, the price will be calculated by the square meters.")
                    }}
                    <br />
                    {{
                      // prettier-ignore
                      $t("If lm is selected, the price will be calculated by the length meters.")
                    }}
                    <br />
                    {{
                      // prettier-ignore
                      $t("If sheet is selected, the price will be calculated by the number of sheets.")
                    }}
                    <br />
                    <br />
                    <font-awesome-icon
                      :icon="['fal', 'arrow-right']"
                      class="fa-fw ml-auto text-2xl text-theme-500"
                    />
                  </div>
                </template>
              </VMenu>
            </label>
            <span class="relative">
              <UISelector
                v-if="selected_category.price_build.full_calculation"
                :value="item.calculation_method"
                :options="[
                  { value: 'qty', label: $t('quantity') },
                  { value: 'sqm', label: $t('square meter') },
                  { value: 'lm', label: $t('length meter') },
                  { value: 'sheet', label: $t('sheet') },
                ]"
                class="!p-2"
                @input="
                  update_item({
                    key: 'calculation_method',
                    value: $event,
                  })
                "
              />
              <UISelector
                v-else
                :value="item.calculation_method"
                :options="[
                  { value: 'qty', label: $t('quantity') },
                  { value: 'sqm', label: $t('square meter') },
                  { value: 'lm', label: $t('length meter') },
                ]"
                class="!p-2"
                @input="
                  update_item({
                    key: 'calculation_method',
                    value: $event,
                  })
                "
              />
            </span>
          </section>
          <!-- UNIT -->
          <template v-if="!item.dynamic || (item.dynamic && item.dynamic_type === 'format')">
            <span class="relative mb-4 block">
              <label for="unit" class="text-sm font-bold uppercase tracking-widest">
                <font-awesome-icon :icon="['fal', 'ruler']" class="fa-fw mr-2 text-gray-500" />
                {{ $t("Unit") }}
              </label>
              <UISelector
                :options="[
                  { value: 'mm', label: 'MM' },
                  { value: 'cm', label: 'CM' },
                  { value: 'm', label: 'M' },
                ]"
                placeholder="Unit"
                :value="item.unit || 'mm'"
                name="unit"
                class="!p-2"
                @input="
                  update_item({
                    key: 'unit',
                    value: $event,
                  })
                "
              />
            </span>
            <!-- WIDTH -->
            <div class="rounded bg-gray-100 p-2 dark:bg-gray-800">
              <span class="relative block">
                <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
                  <font-awesome-icon
                    :icon="['fal', 'arrows-left-right']"
                    class="fa-fw mr-2 text-gray-500"
                  />
                  {{ $t("width") }}
                </label>
                <input
                  type="number"
                  :value="item.width || 0"
                  name="incremented_by"
                  class="input w-full p-2"
                  @change="
                    update_item({
                      key: 'width',
                      value: $event.target.value,
                    })
                  "
                />
              </span>
              <div
                v-if="item.dynamic && item.dynamic_type === 'format'"
                class="grid grid-cols-2 gap-4"
              >
                <!-- MINIMUM WIDTH FIELD -->
                <span class="relative mt-4 block">
                  <label for="maximum_width" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrows-left-right']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Min. width") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Minimum width"
                    :model-value="item.minimum_width || 0"
                    name="minimum_width"
                    @update:model-value="
                      update_item({
                        key: 'minimum_width',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MINIMUM WIDTH FIELD end -->
                <!-- MAXIMUM WIDTH FIELD -->
                <span class="relative mt-4 block">
                  <label for="maximum_width" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrows-left-right']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Max. width") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Maximum width"
                    :model-value="item.maximum_width || 0"
                    name="maximum_width"
                    @update:model-value="
                      update_item({
                        key: 'maximum_width',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MAXIMUM WIDTH FIELD end -->
              </div>
            </div>
            <!-- HEIGHT -->
            <div class="my-4 rounded bg-gray-100 p-2 dark:bg-gray-800">
              <span class="relative block">
                <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
                  <font-awesome-icon
                    :icon="['fal', 'arrows-up-down']"
                    class="fa-fw mr-2 text-gray-500"
                  />
                  {{ $t("height") }}
                </label>
                <input
                  type="number"
                  :value="item.height || 0"
                  name="incremented_by"
                  class="input w-full p-2"
                  @change="
                    update_item({
                      key: 'height',
                      value: $event.target.value,
                    })
                  "
                />
              </span>
              <div
                v-if="item.dynamic && item.dynamic_type === 'format'"
                class="grid grid-cols-2 gap-4"
              >
                <!-- MINIMUM HEIGHT FIELD -->
                <span class="relative my-4 block">
                  <label for="maximum_height" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrows-up-down']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Min. height") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Minimum height"
                    :model-value="item.minimum_height || 0"
                    name="minimum_height"
                    @update:model-value="
                      update_item({
                        key: 'minimum_height',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MINIMUM HEIGHT FIELD end -->
                <!-- MAXIMUM HEIGHT FIELD -->
                <span class="relative mt-4 block">
                  <label for="maximum_height" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrows-up-down']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Max. height") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Maximum height"
                    :model-value="item.maximum_height || 0"
                    name="maximum_height"
                    @update:model-value="
                      update_item({
                        key: 'maximum_height',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MAXIMUM HEIGHT FIELD end -->
              </div>
            </div>
            <!-- LENGTH -->
            <div v-if="is3D" class="rounded bg-gray-100 p-2 dark:bg-gray-800">
              <span class="relative block">
                <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
                  <font-awesome-icon
                    :icon="['fal', 'arrow-up-right-and-arrow-down-left-from-center']"
                    class="fa-fw mr-2 text-gray-500"
                  />
                  {{ $t("length") }}
                </label>
                <input
                  type="number"
                  :value="item.length || 0"
                  name="incremented_by"
                  class="input w-full p-2"
                  @change="
                    update_item({
                      key: 'length',
                      value: $event.target.value,
                    })
                  "
                />
              </span>
              <div
                v-if="item.dynamic && item.dynamic_type === 'format'"
                class="grid grid-cols-2 gap-4"
              >
                <!-- MINIMUM LENGTH FIELD -->
                <span class="relative mt-4 block">
                  <label for="maximum_length" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrow-up-right-and-arrow-down-left-from-center']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Min. length") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Minimum length"
                    :model-value="item.minimum_length || 0"
                    name="minimum_length"
                    @update:model-value="
                      update_item({
                        key: 'minimum_length',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MINIMUM LENGTH FIELD end -->
                <!-- MAXIMUM LENGTH FIELD -->
                <span class="relative mt-4 block">
                  <label for="maximum_length" class="text-sm font-bold uppercase tracking-widest">
                    <font-awesome-icon
                      :icon="['fal', 'arrow-up-right-and-arrow-down-left-from-center']"
                      class="fa-fw mr-2 text-gray-500"
                    />
                    {{ $t("Max. length") }}
                  </label>
                  <UIInputText
                    type="number"
                    size="md"
                    placeholder="Maximum length"
                    :model-value="item.maximum_length || 0"
                    name="maximum_height"
                    @update:model-value="
                      update_item({
                        key: 'maximum_length',
                        value: $event,
                      })
                    "
                  />
                </span>
                <!-- MAXIMUM LENGTH FIELD end -->
              </div>
            </div>
          </template>
          <!-- START_ON -->
          <span
            v-if="item.dynamic && (item.dynamic_type === 'pages' || item.dynamic_type === 'sides')"
            class="relative mb-8 block"
          >
            <label for="start_on" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon :icon="['fal', 'flag-pennant']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("start on") }}
            </label>
            <input
              type="number"
              :value="item.start_on"
              name="start_on"
              class="input w-full px-2 py-1"
              @change="
                update_item({
                  key: 'start_on',
                  value: $event.target.value,
                })
              "
            />
          </span>
          <!-- END_ON -->
          <span
            v-if="item.dynamic && (item.dynamic_type === 'pages' || item.dynamic_type === 'sides')"
            class="relative my-9 block"
          >
            <label for="end_on" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon
                :icon="['fal', 'flag-checkered']"
                class="fa-fw mr-2 text-gray-500"
              />
              {{ $t("end on") }}
            </label>
            <input
              type="number"
              :value="item.end_on"
              name="start_on"
              class="input w-full px-2 py-1"
              @change="
                update_item({
                  key: 'end_on',
                  value: $event.target.value,
                })
              "
            />
          </span>
          <!-- GENERATE -->
          <div
            v-if="item.dynamic && (item.dynamic_type === 'pages' || item.dynamic_type === 'sides')"
            class="my-4 flex"
          >
            <label for="dynamic" class="mr-auto text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon
                :icon="['fal', 'cubes-stacked']"
                class="fa-fw mr-2 text-gray-500"
              />
              {{ $t("generate") }}
            </label>
            <UISwitch
              :value="item.generate"
              name="generate"
              @input="
                update_item({
                  key: 'generate',
                  value: $event,
                })
              "
            />
          </div>
          <!-- INCREMENTAL_BY -->
          <span
            v-if="item.dynamic && (item.dynamic_type === 'pages' || item.dynamic_type === 'sides')"
            class="relative my-4 block"
          >
            <label for="incremented_by" class="text-sm font-bold uppercase tracking-widest">
              <font-awesome-icon :icon="['fal', 'angles-right']" class="fa-fw mr-2 text-gray-500" />
              {{ $t("incremented by") }}
            </label>
            <input
              type="number"
              :value="item.incremental_by"
              name="incremented_by"
              class="input w-full p-2"
              @change="
                update_item({
                  key: 'incremental_by',
                  value: $event.target.value,
                })
              "
            />
          </span>
        </div>
      </section>
      <div v-else>
        <div class="flex h-64 items-center justify-center">
          <font-awesome-icon :icon="['fal', 'exclamation-triangle']" class="fa-4x text-gray-500" />
          <span class="ml-4 text-2xl text-gray-500">
            {{
              //prettier-ignore
              $t("Configurations updatable from assortment, this is saved in relation to the category.")
            }}
          </span>
        </div>
      </div>
    </div>
  </article>
</template>

<script>
import moment from "moment";
import { mapState, mapMutations } from "vuex";
import _ from "lodash";
import UIImageSelector from "~/components/global/ui/UIImageSelector.vue";

export default {
  props: {
    configEditable: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const api = useAPI();
    return { api };
  },
  data() {
    return {
      isDynamic: false,
      hasAdditional: false,
      is3D: this.item && this.item.dimension === "3d",
      moment: moment,
      linked: {},
      filter: "",
      parents: [],
      translate: false,
      calcRefType: null,
    };
  },
  computed: {
    ...mapState({
      item: (state) => state.assortmentsettings.item,
      selected_category: (state) => state.product_wizard.selected_category,
    }),
  },
  watch: {
    filter: _.debounce(function (v) {
      this.getAllItems({ page: 1, filter: v });
    }, 300),
    item: {
      deep: true,
      handler(v) {
        return v;
      },
      immediate: true,
    },
  },
  created() {
    if (this.item && this.item.dynamic) {
      this.setIsDynamic(this.item.dynamic);
    }
    if (this.item && this.item.additional?.calc_ref_type) {
      this.setCalcRefType(this.item.additional?.calc_ref_type);
    }
    if (this.item) {
      this.item.input_type = "radio";
    }
  },
  methods: {
    ...mapMutations({
      update_item: "assortmentsettings/update_item",
    }),
    setIs3D(dimension) {
      this.is3D = dimension === "3d" ? true : false;
      this.update_item({
        key: "dimension",
        value: dimension,
      });
    },
    setIsDynamic(dynamic) {
      this.isDynamic = dynamic;
      this.update_item({
        key: "dynamic",
        value: dynamic,
      });
      this.update_item({
        key: "dynamic_keys",
        value: [],
      });
    },
    setHasAdditional(value) {
      this.hasAdditional = value;
    },
    setCalcRefType(value) {
      this.calcRefType = value;
    },
    async getAllItems(e) {
      let url = "";
      switch (this.type) {
        case "box":
          url = `boxes`;
          break;
        case "option":
          url = `options`;
          break;

        default:
          break;
      }

      await this.api.get(`/${url}?filter=${e.filter ? e.filter : ""}`).then((response) => {
        this.parents = response.data;
      });
    },
  },
};
</script>

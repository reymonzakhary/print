<template>
  <div>
    <template v-for="item in list" :key="item.id">
      <div
        class="flex items-center justify-between px-2 py-1 normal-case transition-colors duration-100 rounded cursor-pointer hover:bg-gray-200 focus:outline-none group"
        :class="{
          'font-bold bg-gray-200': showDetails === item.id,
        }"
        @click="
          showDetails !== item.id
            ? (showDetails = item.id)
            : (showDetails = false)
        "
      >
        <div class="w-full">
          <p class="flex items-center justify-between">
            {{ item.label || item.name }}
            <font-awesome-icon
              :icon="[
                showDetails === item.id ? 'fas' : 'fal',
                showDetails === item.id ? 'caret-down' : 'caret-right',
              ]"
            />
          </p>

          <transition name="slide">
            <div
              v-if="showDetails === item.id"
              class="my-2 font-normal text-gray-500"
            >
              <p class="mb-2">
                {{ $t("input") }} {{ $t("type") }}:
                {{ item.input_type }}
              </p>
              <p
                class="text-sm text-blue-500"
                @click="copyTag(item.short_code)"
              >
                {{ item.short_code }}
              </p>
            </div>
          </transition>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { mapState, mapMutations } from "vuex";

export default {
  props: {
    list: Array,
  },
  setup() {
    const { addToast } = useToastStore();
    return { addToast }
  },
  data() {
    return {
      showDetails: false,
      showRemoveItem: false,
      showEditItem: false,
      copiedText: "",
    };
  },
  computed: {
    ...mapState({
      selected_item: (state) => state.templates.selected_item,
    }),
  },
  methods: {
    ...mapMutations({
      set_selected_item: "templates/set_selected_item",
    }),
    copyTag(tag) {
      const textarea = document.createElement("textarea");
      //Settings its value to the thing you want to copy
      textarea.value = tag;
      //Appending the textarea to body
      document.body.appendChild(textarea);
      //Selecting its content
      textarea.focus();
      textarea.select();
      //Copying the selected content to clipboard
      document.execCommand("copy");
      //Removing the textarea
      document.body.removeChild(textarea);

      this.addToast({
        type: "success",
        message: "Copied",
      });
    },
  },
};
</script>

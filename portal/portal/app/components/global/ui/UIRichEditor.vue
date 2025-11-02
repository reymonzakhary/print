<template>
  <div>
    <client-only>
      <Editor
        v-model="newValue"
        tinymce-script-src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js"
        :init="{
          license_key: 'gpl',
          plugins: 'lists link image table code help wordcount autoresize',
        }"
      />
    </client-only>
  </div>
</template>

<script>
import _ from "lodash";
import Editor from "@tinymce/tinymce-vue";

export default {
  name: "UIRichEditor",
  components: {
    Editor,
  },
  props: {
    value: {
      type: String,
      default: "",
    },
  },
  emits: ["input"],
  data() {
    return {
      editor: null,
      newValue: "",
    };
  },
  watch: {
    newValue: _.debounce(function (v) {
      this.$emit("input", v);
    }, 500),
  },
  mounted() {
    this.newValue = this.value && this.value.length > 0 ? this.value : "";
  },
};
</script>

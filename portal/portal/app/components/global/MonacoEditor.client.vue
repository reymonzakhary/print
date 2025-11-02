<template>
  <div ref="editorElement"></div>
</template>

<script setup>
import * as monaco from "monaco-editor";
import editorWorker from "monaco-editor/esm/vs/editor/editor.worker?worker";
import jsonWorker from "monaco-editor/esm/vs/language/json/json.worker?worker";
import cssWorker from "monaco-editor/esm/vs/language/css/css.worker?worker";
import htmlWorker from "monaco-editor/esm/vs/language/html/html.worker?worker";
import tsWorker from "monaco-editor/esm/vs/language/typescript/ts.worker?worker";

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  language: {
    type: String,
    default: "plaintext",
  },
  options: {
    type: Object,
    default: () => ({}),
  },
});
const emit = defineEmits(["update:modelValue", "onLoad"]);
const editorElement = ref(null);
let editor, model;

onMounted(() => {
  self.MonacoEnvironment = {
    getWorker: function (_, label) {
      switch (label) {
        case "json":
          return new jsonWorker();
        case "css":
        case "scss":
        case "less":
          return new cssWorker();
        case "html":
        case "handlebars":
        case "razor":
          return new htmlWorker();
        case "typescript":
        case "javascript":
          return new tsWorker();
        default:
          return new editorWorker();
      }
    },
  };

  nextTick(() => {
    editor = monaco.editor.create(editorElement.value, {
      ...props.options,
    });
    model = monaco.editor.createModel(props.modelValue, props.language);
    editor.layout();
    editor.setModel(model);
    editor.onDidChangeModelContent(() => {
      emit("update:modelValue", editor.getValue());
    });

    emit("onLoad", { editor, model });
  });
});

watch(
  () => props.modelValue,
  (value) => {
    if (editor && editor.getValue() !== value) {
      editor.setValue(value);
    }
  },
);

watch(
  () => props.language,
  (value) => {
    if (model) {
      monaco.editor.setModelLanguage(model, value);
    }
  },
);

watch(
  () => props.options,
  (value) => {
    if (editor) {
      editor.updateOptions(value);
    }
  },
);

defineExpose({
  editor,
  model,
});

onBeforeUnmount(() => {
  editor?.dispose();
  model?.dispose();
});
</script>

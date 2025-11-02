<template>
  <div class="flex h-full flex-col">
    <UIInputText
      v-if="searchable"
      v-model="searchQuery"
      name="search-tags"
      :placeholder="$t('Search tags...')"
      size="sm"
      class="mb-3 w-full flex-shrink-0"
      :prefix="['fal', 'search']"
    />

    <div class="flex-1 overflow-hidden">
      <ul class="h-full space-y-1 overflow-y-auto overflow-x-hidden">
        <li v-for="tag in filteredTags" :key="tag.name" class="w-full">
          <StudioMagicTagListItem :tag="tag" :show-copy="showCopy" @click="handleTagClick(tag)" />
        </li>
      </ul>

      <div
        v-if="filteredTags.length === 0"
        class="flex flex-col items-center justify-center gap-2 py-8 text-gray-400"
      >
        <font-awesome-icon :icon="['fal', 'inbox']" />
        <p>{{ $t("No tags found") }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  tags: {
    type: Array,
    required: true,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  grouped: {
    type: Boolean,
    default: false,
  },
  showCopy: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["select"]);

const { t: $t } = useI18n();
const { addToast } = useToastStore();

const searchQuery = ref("");

// Filter tags based on search
const filteredTags = computed(() => {
  if (!searchQuery.value) return props.tags;

  const query = searchQuery.value.toLowerCase();
  return props.tags.filter(
    (tag) =>
      tag.display.toLowerCase().includes(query) ||
      tag.name.toLowerCase().includes(query) ||
      tag.description?.toLowerCase().includes(query),
  );
});

// Handle tag click
const handleTagClick = async (tag) => {
  if (props.showCopy) {
    try {
      await navigator.clipboard.writeText(tag.name);
      addToast({
        type: "success",
        message: $t("Tag copied to clipboard"),
      });
    } catch {
      // Fallback for older browsers
      const textarea = document.createElement("textarea");
      textarea.value = tag.name;
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);

      addToast({
        type: "success",
        message: $t("Tag copied to clipboard"),
      });
    }
  }

  emit("select", tag);
};
</script>

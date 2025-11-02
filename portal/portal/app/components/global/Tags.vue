<template>
  <div>
    <div v-if="selectedTags" class="flex flex-wrap text-xs">
      <span
        v-for="tag in selectedTags"
        :key="`tag_${tag.id}`"
        :style="`background-color: ${tag.hex}`"
        class="flex items-center pl-2 pr-1 m-1 text-white rounded rounded-l-full"
      >
        <span class="w-1 h-1 mr-2 bg-white rounded-full"></span>
        {{ tag.name }}
        <font-awesome-icon
          :icon="['fad', 'circle-xmark']"
          class="ml-2 cursor-pointer hover:text-gray-300"
          @click="removeTag(tag)"
        />
      </span>

      <button
        class="ml-auto text-xs text-theme-400 hover:text-theme-600"
        @click="toggleNewTag"
      >
        <font-awesome-icon :icon="['fal', 'tags']" />
        <font-awesome-icon :icon="['fal', 'plus']" class="mr-1" />
        {{ $t("new tag") }}
      </button>
    </div>

    <div v-if="newTag" class="relative">
      <input
        ref="tagFilter"
        v-model="filter"
        type="text"
        class="w-full p-0 pl-6 text-sm input"
        :class="{ 'rounded-none rounded-t': filter.length > 0 }"
      />
      <font-awesome-icon
        :icon="['fal', 'tags']"
        class="absolute top-0 left-0 mx-1 mt-2 text-gray-500 fa-sm"
      />

      <section
        v-if="filter.length > 0"
        class="flex flex-wrap p-2 bg-gray-200 rounded-b"
      >
        <div
          class="flex items-center justify-between w-full px-1 my-1 mt-1 text-sm bg-gray-100 rounded"
        >
          <div class="relative">
            <button
              class="relative flex items-center"
              @click="showColors = !showColors"
            >
              <div
                class="w-3 h-3 rounded-full"
                :style="`background-color: ${tagColor}`"
              ></div>
              <font-awesome-icon
                :icon="['fal', 'caret-down']"
                class="mx-1 text-base text-gray-500 fa-sm"
              />
            </button>

            <div
              v-if="showColors"
              class="absolute left-0 flex flex-wrap w-24 p-2 bg-white rounded shadow top-5"
            >
              <button
                v-for="availableColor in tagColors"
                :key="'color_' + availableColor"
                class="w-3 h-3 m-1 rounded-full"
                :style="`background-color: ${availableColor}`"
                @click="(tagColor = availableColor), (showColors = false)"
              ></button>
            </div>
          </div>

          <span
            :style="`background-color: ${tagColor}`"
            class="flex items-center pl-2 pr-1 my-1 text-white rounded rounded-l-full"
          >
            <span class="w-1 h-1 mr-2 bg-white rounded-full"></span>
            {{ filter }}
          </span>

          <button
            class="px-2 align-text-bottom rounded-full text-theme-400 hover:bg-theme-100"
            @click="addTag()"
          >
            +
          </button>
        </div>

        <button
          v-for="tag in tags"
          :key="'tag' + tag.id"
          :style="`background-color: ${tag.hex}`"
          class="flex items-center pl-2 pr-1 mx-1 my-1 text-sm text-white rounded rounded-l-full hover:shadow-md"
          @click="addToSelectedTags(tag.id)"
        >
          <span class="w-1 h-1 mr-2 bg-white rounded-full"></span>
          {{ tag.name }}
        </button>
      </section>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions, mapMutations, mapGetters } from "vuex";
import _ from "lodash";

export default {
  props: {
    selectedTags: {
      type: Array,
      required: true,
    },
  },
  emits: ["onUpdateTags"],
  data() {
    return {
      newTag: false,
      filter: "",
      updatedTags: [],
      tagColor: "#3b82f6",
      tagColors: [
        "#ef4444",
        "#f97316",
        "#f59e0b",
        "#eab308",
        "#22c55e",
        "#10b981",
        "#14b8a6",
        "#06b6d4",
        "#0ea5e9",
        "#3b82f6",
        "#6366f1",
        "#8b5cf6",
        "#a855f7",
        "#d946ef",
        "#ec4899",
        "#f43f5e",
      ],
      showColors: false,
    };
  },
  computed: {
    ...mapState({
      tags: (state) => state.tags.tags,
    }),
    ...mapGetters({
      storeTags: "tags/tagsBy Id",
    }),
  },
  watch: {
    tags: {
      handler(v) {
        return v;
      },
      deep: true,
    },
    selectedTags: {
      handler(v) {
        v.forEach((tag) => {
          this.updatedTags.push(tag.id);
        });
      },
      deep: true,
    },

    filter: _.debounce(function (v) {
      this.get_tags(v);
    }, 300),
  },
  created() {
    this.tagColor =
      this.tagColors[Math.floor(Math.random() * this.tagColors.length)];

    if (this.selectedTags) {
      this.selectedTags.forEach((tag) => {
        this.updatedTags.push(tag.id);
      });
    }
  },
  beforeUnmount() {
    this.set_tags({});
  },

  methods: {
    ...mapMutations({
      set_tags: "tags/set_tags",
    }),
    ...mapActions({
      add_tag: "tags/add_tag",
      get_tags: "tags/get_tags",
    }),

    toggleNewTag() {
      this.newTag = !this.newTag;
      this.$nextTick(() => {
        if (this.newTag) {
          this.$refs.tagFilter.focus();
        }
      });
    },
    addTag() {
      this.add_tag({ name: this.filter, hex: this.tagColor });
      setTimeout(() => {
        const pocket = this.tags.length - 1;
        this.addToSelectedTags(this.tags[pocket].id);
      }, 200);
    },

    removeTag(sendtag) {
      const i = this.updatedTags.findIndex((id) => id === sendtag.id);
      this.updatedTags.splice(i, 1);
      this.selectedTags.splice(i, 1);
      this.$emit("onUpdateTags", this.updatedTags);
    },
    addToSelectedTags(id) {
      if (!this.updatedTags.includes(id)) {
        this.updatedTags.push(id);

        // remove accidental duplicates
        this.updatedTags = [...new Set(this.updatedTags)];
        this.$emit("onUpdateTags", this.updatedTags);

        // cleanup
        this.updatedTags = [];
        this.filter = "";
        this.newTag = false;
      }
    },
  },
};
</script>

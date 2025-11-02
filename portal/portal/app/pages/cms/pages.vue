<template>
  <main class="flex flex-wrap w-full">
    <SeoPreview
      v-if="viewSeo"
      :data="editableResource"
      :url="url + urlPrefix(editableResource.uri) + '/'"
    />

    <ResourceRemoveItem
      v-if="showRemoveItem"
      :item="editableResource"
      type="resource"
      @on-close="showRemoveItem = false"
    >
    </ResourceRemoveItem>

    <ResourceMenu></ResourceMenu>

    <div class="w-full h-full overflow-y-auto lg:w-5/6 xl:w-4/6">
      <!-- <div> -->
      <section
        v-if="editableResource && Object.keys(editableResource).length > 0"
        class="w-full p-4"
      >
        <div
          class="sticky flex items-center justify-between w-full px-3 pt-2 rounded -top-4 bg-theme-400"
        >
          <h2
            class="mr-4 font-bold tracking-wider uppercase text-themecontrast-400"
          >
            <font-awesome-icon
              :icon="['fas', 'folder-tree']"
              class="text-theme-100"
            />
            {{ editableResource.title }}
            <span class="ml-1font-bold text-theme-100">
              #{{ editableResource.id }}
            </span>
          </h2>
          <div class="">
            <font-awesome-icon
              :icon="['fas', 'earth-europe']"
              class="text-theme-100"
            />
            <button
              class="px-4 py-2 mx-2 text-sm font-bold text-black rounded rounded-b-none bg-theme-100 dark:bg-gray-800 dark:text-theme-100"
            >
              English
            </button>
            <button
              class="px-4 py-2 mx-2 text-sm font-bold text-white rounded rounded-b-none bg-theme-400 hover:bg-gray-100 hover:text-black dark:hover:bg-gray-900 dark:text-theme-100"
            >
              Dutch
            </button>
            <button
              class="px-4 py-2 mx-2 text-sm font-bold text-white rounded rounded-b-none bg-theme-400 hover:bg-gray-100 hover:text-black dark:hover:bg-gray-900 dark:text-theme-100"
            >
              French
            </button>
          </div>
        </div>

        <div class="my-6">
          <h2 class="py-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("navigation") }}
          </h2>

          <div class="flex flex-wrap gap-3 lg:flex-nowrap">
            <div
              class="w-full p-3 bg-white border rounded shadow-md shadow-gray-200 dark:shadow-gray-900 lg:w-1/4 dark:bg-gray-700 dark:text-theme-100 dark:border-gray-900"
            >
              <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                <font-awesome-icon :icon="['fal', 'folder-tree']" />
                {{ $t("title") }}
              </h3>

              <p class="my-1">
                {{ $t("his title is used in the manager") }}
              </p>

              <input
                v-model="editableResource.title"
                type="text"
                class="w-full my-2 input"
                @change="set_resource(editableResource)"
              />
            </div>

            <div
              class="w-full p-3 bg-white border rounded shadow-md shadow-gray-200 dark:shadow-gray-900 lg:w-1/4 dark:bg-gray-700 dark:text-theme-100 dark:border-gray-900"
            >
              <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                <font-awesome-icon :icon="['fal', 'bars']" />
                {{ $t("menu title") }}
              </h3>

              <p class="my-1">
                {{ $t("his title is shown in the menu") }}
              </p>

              <input
                v-model="editableResource.menu_title"
                type="text"
                class="w-full my-2 input"
                @change="set_resource(editableResource)"
              />
            </div>

            <div
              class="w-full p-3 bg-white border rounded shadow-md shadow-gray-200 dark:shadow-gray-900 lg:w-2/4 dark:bg-gray-700 dark:text-theme-100 dark:border-gray-900"
            >
              <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                <font-awesome-icon :icon="['fal', 'link']" />
                {{ $t("url of the page") }}
              </h3>

              <p class="my-1">{{ $t("exact page url") }}</p>

              <div class="flex my-2">
                <span
                  class="flex items-center p-3 my-auto text-sm italic text-gray-500 bg-gray-100 rounded rounded-r-none"
                >
                  {{ url + urlPrefix(editableResource.uri) + "/" }}
                </span>
                <input
                  v-model="editableResource.slug"
                  type="text"
                  class="rounded-l-none input"
                  @change="set_resource(editableResource)"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="my-6">
          <h2 class="py-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("seo") }}
          </h2>

          <div class="flex flex-wrap gap-3 lg:flex-nowrap">
            <div
              class="w-full p-3 bg-white border rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 dark:text-theme-100 dark:border-gray-900"
            >
              <div class="flex justify-between">
                <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                  <font-awesome-icon :icon="['fal', 'window']" />
                  {{ $t("seo title") }}
                </h3>

                <button
                  class="px-2 py-1 text-sm transition duration-75 border rounded-full text-theme-500 border-theme-500 hover:bg-theme-200 hover:text-theme-700 dark:text-theme-400 dark:border-theme-400"
                  @click="viewSeo = true"
                >
                  <font-awesome-icon
                    class="mx-2"
                    :icon="['fal', 'search-location']"
                  />
                  {{ $t("show seo previews") }}
                </button>
              </div>

              <p class="mb-2">
                {{ $t("This title is shown in the browser tab") }}
              </p>

              <div class="flex flex-wrap items-center">
                <div
                  class="flex items-center justify-between w-full px-2 bg-gray-200 rounded-t-lg dark:bg-gray-800 dark:text-theme-100 dark:border-gray-900 md:w-3/5"
                >
                  <span class="flex items-center w-full">
                    <img width="18" class="mx-1" :src="`/icon.png`" alt="" />
                    <input
                      v-model="editableResource.long_title"
                      type="text"
                      class="w-full my-2 text-sm input"
                      @change="set_resource(editableResource)"
                    />
                  </span>

                  <button class="mx-2 mb-1 font-bold cursor-auto">x</button>
                </div>

                <p v-if="editableResource.long_title" class="mx-4">
                  <span
                    class="font-bold"
                    :class="{
                      'text-red-500': editableResource.long_title.length > 70,
                    }"
                  >
                    {{ editableResource.long_title.length }}
                  </span>
                  /70
                  {{ $t("characters") }}
                </p>
              </div>

              <span class="px-1 mt-1 text-sm italic text-gray-500">
                {{ $t("Primary keyword - Secondary keyword | Brandname") }}
              </span>

              <div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-2">
                <div>
                  <h3
                    class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
                  >
                    <font-awesome-icon :icon="['fal', 'image']" />
                    {{ $t("image") }}
                  </h3>

                  <!-- <UploadImage
										v-model="editableResource.image"
										:image="editableResource.image"
                              @input="set_resource(editableResource)"
									/> -->
                </div>

                <div>
                  <h3
                    class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
                  >
                    <font-awesome-icon :icon="['fal', 'file-lines']" />
                    {{ $t("description") }}
                  </h3>

                  <textarea
                    v-model="editableResource.description"
                    class="input"
                    :placeholder="
                      $t('This is a content page for demo purposes')
                    "
                    rows="3"
                    @change="set_resource(editableResource)"
                  >
                  </textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div
          v-if="
            editableResource.variables &&
            editableResource.variables.length > 0 &&
            editableResource.content &&
            editableResource.content.length > 0
          "
          class="my-6"
        >
          <h2 class="py-2 text-sm font-bold tracking-wide uppercase">
            {{ $t("content") }}
          </h2>

          <div
            class="flex flex-wrap w-full p-3 bg-white border rounded shadow-md shadow-gray-200 dark:shadow-gray-900 dark:bg-gray-700 dark:text-theme-100 dark:border-gray-900"
          >
            <section
              v-for="(variable, i) in editableResource.variables"
              :key="'var_' + variable.id"
              class="w-1/2 p-2"
            >
              <template v-for="content in editableResource.content">
                <!-- TEXT -->
                <div
                  v-if="
                    variable.key === 'text' && content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                    <font-awesome-icon :icon="['fal', 'heading']" />
                    {{ variable.label }}
                  </h3>
                  <input
                    v-model="content.value"
                    type="text"
                    :placeholder="variable.placeholder"
                    class="w-full my-2 input"
                    @change="
                      updateContent($event, i, variable),
                        set_resource(editableResource)
                    "
                  />
                </div>

                <!-- TEXTAREA -->
                <div
                  v-if="
                    variable.key === 'long_text' &&
                    content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                    <font-awesome-icon :icon="['fal', 'align-left']" />
                    {{ variable.label }}
                  </h3>
                  <textarea
                    v-model="content.value"
                    class="w-full my-2 input"
                    :placeholder="$t('')"
                    rows="3"
                    @change="set_resource(editableResource)"
                  >
                  </textarea>
                </div>

                <!-- RICHTEXT -->
                <div
                  v-if="
                    variable.key === 'rich_text' &&
                    content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3
                    class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
                  >
                    Richtext
                  </h3>
                  <Editor
                    tinymce-script-src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js"
                    :init="{
                      license_key: 'gpl',
                      plugins: 'lists link image table code help wordcount',
                    }"
                  />
                </div>

                <!-- IMAGE -->
                <div
                  v-if="
                    variable.key === 'file' && content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3
                    class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
                  >
                    <font-awesome-icon :icon="['fal', 'image']" />
                    {{ variable.label }}
                  </h3>
                  <!-- <UploadImage
										@imageChanged="content.value = $event.value"
										:image="
											content.value
												? content.value
												: null
										"
									/> -->
                </div>

                <!-- DATE -->
                <div
                  v-if="
                    variable.key === 'date' && content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                    <font-awesome-icon :icon="['fal', 'align-left']" />
                    {{ variable.label }}
                  </h3>
                  <input
                    v-model="content.value"
                    type="date"
                    class="w-full my-2 input"
                    @change="set_resource(editableResource)"
                  />
                </div>

                <!-- BOOLEAN -->
                <div
                  v-if="
                    variable.key === 'bool' && content.key === variable.name
                  "
                  :key="content.key"
                  class="w-full"
                >
                  <h3 class="py-1 text-xs font-bold tracking-wide uppercase">
                    <font-awesome-icon :icon="['fal', 'align-left']" />
                    {{ variable.label }}
                  </h3>

                  <div
                    class="relative w-10 h-4 mx-2 transition duration-200 ease-linear rounded-full cursor-pointer"
                    :class="[content.value ? 'bg-green-500' : 'bg-gray-300']"
                    @click="
                      (content.bool = !content.value),
                        set_resource(editableResource)
                    "
                  >
                    <label
                      class="absolute left-0 w-4 h-4 mb-2 transition duration-100 ease-linear transform bg-white border-2 rounded-full cursor-pointer"
                      :class="[
                        content.value
                          ? 'translate-x-6 border-green-500'
                          : 'translate-x-0 border-gray-300',
                      ]"
                    >
                      <input
                        type="checkbox"
                        hidden
                        class="w-full h-full appearance-none active:outline-none focus:outline-none"
                      />
                    </label>
                  </div>
                </div>
              </template>
            </section>

            <!-- <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
							<div>
								<h3
									class="py-1 text-sm font-bold tracking-wide uppercase"
								>
									{{ $t("block") }} 1
								</h3>
								<div class="p-3 bg-gray-100 rounded">
									<h3
										class="py-1 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'bars']" />
										{{ $t("heading") }}
									</h3>
									<input
										type="text"
										value="Content page"
										class="w-full my-2 input"
									/>
									<h3
										class="py-1 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'file-lines']" />
										{{ $t("text") }}
									</h3>
									<textarea
										class="my-2 input"
										:placeholder="$t('')"
										rows="3"
									>
									</textarea>
									<h3
										class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'image']" />
										{{ $t("image") }}
									</h3>
									<UploadImage />
								</div>
							</div>

							<div>
								<h3
									class="py-1 text-sm font-bold tracking-wide uppercase"
								>
									{{ $t("block") }} 2
								</h3>
								<div class="p-3 bg-gray-100 rounded">
									<h3
										class="py-1 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'bars']" />
										{{ $t("heading") }}
									</h3>
									<input
										type="text"
										value="Content page"
										class="w-full my-2 input"
									/>
									<h3
										class="py-1 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'file-lines']" />
										{{ $t("text") }}
									</h3>
									<textarea
										class="my-2 input"
										:placeholder="$t('')"
										rows="3"
									>
									</textarea>
									<h3
										class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
									>
										<font-awesome-icon :icon="['fal', 'image']" />
										{{ $t("image") }}
									</h3>
									<UploadImage />
								</div>
							</div>
						</section>
                  
						<section class="my-8">
							<div class="flex justify-between mb-2">
								<h3
									class="py-1 text-sm font-bold tracking-wide uppercase"
								>
									{{ $t("carousel") }}
									<span class="font-medium capitalize">
										{{ $t("max items") }}: 6
									</span>
								</h3>
								<button class="text-sm capitalize text-theme-500 ">
									<font-awesome-icon :icon="['fal', 'plus']" />
									{{ $t("add item") }}
								</button>
							</div>

							<div
								class="grid grid-cols-1 gap-4 p-3 border rounded lg:grid-cols-2"
							>
								<div>
									<h3
										class="py-1 text-sm font-bold tracking-wide uppercase"
									>
										{{ $t("slide") }} 1
									</h3>
									<div class="p-3 bg-gray-100 rounded">
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'bars']" />
											{{ $t("heading") }}
										</h3>
										<input
											type="text"
											value="Content page"
											class="w-full my-2 input"
										/>
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon
												:icon="['fal', 'file-lines']"
											/>
											{{ $t("text") }}
										</h3>
										<textarea
											class="my-2 input"
											:placeholder="$t('')"
											rows="3"
										>
										</textarea>
										<h3
											class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'image']" />
											{{ $t("image") }}
										</h3>
										<UploadImage />
									</div>
								</div>

								<div>
									<h3
										class="py-1 text-sm font-bold tracking-wide uppercase"
									>
										{{ $t("slide") }} 2
									</h3>
									<div class="p-3 bg-gray-100 rounded">
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'bars']" />
											{{ $t("heading") }}
										</h3>
										<input
											type="text"
											value="Content page"
											class="w-full my-2 input"
										/>
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon
												:icon="['fal', 'file-lines']"
											/>
											{{ $t("text") }}
										</h3>
										<textarea
											class="my-2 input"
											:placeholder="$t('')"
											rows="3"
										>
										</textarea>
										<h3
											class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'image']" />
											{{ $t("image") }}
										</h3>
										<UploadImage />
									</div>
								</div>
							</div>
						</section>
                  
						<section class="my-8">
							<div class="flex justify-between mb-2">
								<h3
									class="py-1 text-sm font-bold tracking-wide uppercase"
								>
									{{ $t("selected products") }}
									<span class="font-medium capitalize">
										{{ $t("max items") }}: {{ $t("unlimited") }}
									</span>
								</h3>
								<button class="text-sm capitalize text-theme-500 ">
									<font-awesome-icon :icon="['fal', 'plus']" />
									{{ $t("add item") }}
								</button>
							</div>

							<div
								class="grid grid-cols-1 gap-4 p-3 border rounded lg:grid-cols-2"
							>
								<div>
									<h3
										class="py-1 text-sm font-bold tracking-wide uppercase"
									>
										{{ $t("slide") }} 1
									</h3>
									<div class="p-3 bg-gray-100 rounded">
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'bars']" />
											{{ $t("heading") }}
										</h3>
										<input
											type="text"
											value="Content page"
											class="w-full my-2 input"
										/>
										<h3
											class="py-1 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon
												:icon="['fal', 'file-lines']"
											/>
											{{ $t("text") }}
										</h3>
										<textarea
											class="my-2 input"
											:placeholder="$t('')"
											rows="3"
										>
										</textarea>
										<h3
											class="py-1 my-2 text-xs font-bold tracking-wide uppercase"
										>
											<font-awesome-icon :icon="['fal', 'image']" />
											{{ $t("image") }}
										</h3>
										<UploadImage />
									</div>
								</div>

								<div class="flex items-center justify-center ">
									<button
										class="w-40 h-24 text-sm capitalize bg-gray-100 rounded hover:bg-gray-200 focus:outline-none text-theme-500"
									>
										<font-awesome-icon :icon="['fal', 'plus']" />
										{{ $t("select product") }}
									</button>
								</div>
							</div>
						</section> -->
          </div>
        </div>
      </section>

      <section v-else class="w-full p-4 lg:w-5/6 xl:w-4/6">
        <div
          class="flex flex-col flex-wrap items-center justify-center w-full h-full text-center"
        >
          <p class="text-xl font-bold text-gray-400">
            {{ $t("No resource selected") }}
          </p>

          <div class="flex items-start justify-center my-8">
            <font-awesome-icon
              :icon="['fal', 'clouds']"
              class="m-4 text-gray-300 fa-3x"
            />
            <font-awesome-icon
              :icon="['fad', 'window']"
              class="my-4 text-gray-400 fa-5x"
            />
            <font-awesome-icon
              :icon="['fal', 'clouds']"
              class="my-4 text-gray-300 fa-2x"
            />
          </div>
        </div>
      </section>
      <!-- </div> -->
    </div>

    <ResourceOptions
      v-if="resource && Object.keys(resource).length > 0"
      :url="'http://' + url + editableResource.uri"
      class="pr-4 lg:w-5/6 xl:w-1/6"
    ></ResourceOptions>
  </main>
</template>

<script>
import { mapState, mapMutations, mapActions } from "vuex";

import Editor from "@tinymce/tinymce-vue";

export default {
  data() {
    return {
      tree: [],
      viewSeo: false,
      showRemoveItem: false,
      url: "",
      href: "",

      editableResource: {},
    };
  },
  head() {
    return {
      title: `${this.$t("pages")} | Prindustry Manager`,
    };
  },
  mounted() {
    if (window) {
      this.url = window.location.hostname;
      this.href = window.location.href;
    }

    this.get_tree();
    this.get_templates();

    if (this.resource) {
      this.editableResource = { ...this.resource };
    }
  },
  computed: {
    ...mapState({
      selected_item: (state) => state.resources.selected_item,
      resource: (state) => state.resources.resource,
    }),
  },
  watch: {
    selected_item() {
      this.get_resource(this.selected_item.id);
    },
    resource(newVal) {
      this.editableResource = { ...newVal };
    },
    editableResource() {
      if (this.editableResource.content) {
        // this.editableResource.content = [];
        for (let i = 0; i < this.editableResource.variables.length; i++) {
          const variable = this.editableResource.variables[i];
          if (this.editableResource.content[i] === undefined) {
            this.editableResource.content.push({
              key: variable.name,
              value: "",
              type: variable.data_type,
            });
          }
        }
      }
    },
  },
  methods: {
    ...mapMutations({
      set_resource: "resources/set_resource",
    }),
    ...mapActions({
      get_tree: "resources/get_tree",
      get_resource: "resources/get_resource",
      get_resource_groups: "resources/get_resource_groups",
      get_templates: "templates/get_templates",
    }),
    urlPrefix(str) {
      const n = str.lastIndexOf("/");
      const result = str.substring(0, n);
      return result;
    },
    updateContent(e, i, v) {
      if (this.editableResource.content === null) {
        this.editableResource.content = [];
      }

      if (this.editableResource.content[i].key !== v.name) {
        this.editableResource.content[i] = {
          key: v.name,
          value: "",
          type: v.data_type,
        };
      }

      this.editableResource.content[i].key = v.name;
      this.editableResource.content[i].value = e.target.value;
      this.editableResource.content[i].type = v.data_type;
    },
  },
  components: {
    Editor: Editor,
  },
};
</script>

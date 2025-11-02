export const useStandardizationRepository = () => {
  const { $api } = useNuxtApp();

  const categories = ref([]);
  const boxes = ref([]);
  const options = ref([]);

  const pagination = ref({});
  const boxPagination = ref({});
  const optionPagination = ref({});

  // Category methods
  function populateCategories(response) {
    categories.value = response.data;
    pagination.value = response.meta;
  }

  function addSingleCategory(category) {
    categories.value.unshift(category.data);
  }

  // data retrieval methods
  async function getCategories(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(`/categories?per_page=${perPage}&page=${page}&filter=${filter}`, {
      method: "GET",
    });
    return response;
  }

  async function getUnmatchedCategories() {
    const response = await $api("/unmatched/categories", { method: "GET" });
    return response.data;
  }

  async function getMatchedCategories() {
    const response = await $api("/matched/categories", { method: "GET" });
    return response.data;
  }

  async function getUnlinkedCategories(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(
      `/unlinked/categories?per_page=${perPage}&page=${page}&filter=${filter}`,
      { method: "GET" },
    );
    return response;
  }

  async function updateCategory(slug, data) {
    data.checked = !!data.checked;
    const response = await $api(`/categories/${slug}`, {
      method: "PUT",
      body: data,
    });
    return response;
  }

  async function newCategory(data) {
    const response = await $api(`/categories`, {
      method: "POST",
      body: data,
    });
    return response;
  }

  async function deleteCategory(data, force) {
    const response = await $api(`/categories/${data.slug}?force=${force}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function deleteUnmatchedCategory(data) {
    const response = await $api(`unmatched/categories/${data.id}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function attachCategory(slug_master, data_slave) {
    const response = await $api(`categories/${slug_master}/attach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function detachCategory(slug_master, data_slave) {
    const response = await $api(`categories/${slug_master}/detach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function mergeCategories(data) {
    const response = await $api(`/merge/categories`, {
      method: "POST",
      body: {
        name: data.name,
        categories: data.categories,
        type: data.type,
        new: data.new,
      },
    });
    return response.data;
  }

  // Box methods
  function populateBoxes(response) {
    boxes.value = response.data;
    boxPagination.value = response.meta ?? response;
  }

  function addSingleBox(box) {
    boxes.value.unshift(box.data);
  }

  async function getBoxes(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(`/boxes?per_page=${perPage}&page=${page}&filter=${filter}`, {
      method: "GET",
    });
    return response;
  }

  async function getUnmatchedBoxes() {
    const response = await $api("/unmatched/boxes", { method: "GET" });
    return response.data;
  }

  async function getMatchedBoxes() {
    const response = await $api("/matched/boxes", { method: "GET" });
    return response.data;
  }
  async function getUnlinkedBoxes(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(
      `/unlinked/boxes?per_page=${perPage}&page=${page}&filter=${filter}`,
      { method: "GET" },
    );
    return response;
  }

  async function updateBox(slug, data) {
    data.checked = !!data.checked;
    const response = await $api(`/boxes/${slug}`, {
      method: "PUT",
      body: data,
    });
    return response;
  }

  async function newBox(data) {
    const response = await $api(`/boxes`, {
      method: "POST",
      body: data,
    });
    return response;
  }

  async function deleteBox(data, force) {
    const response = await $api(`/boxes/${data.slug}?force=${force}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function deleteUnmatchedBox(data) {
    const response = await $api(`unmatched/boxes/${data.id}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function attachBox(slug_master, data_slave) {
    const response = await $api(`boxes/${slug_master}/attach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function detachBox(slug_master, data_slave) {
    const response = await $api(`boxes/${slug_master}/detach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function mergeBoxes(data) {
    const response = await $api(`/merge/boxes`, {
      method: "POST",
      body: {
        name: data.name,
        boxes: data.boxes,
        type: data.type,
        new: data.new,
      },
    });
    return response.data;
  }

  // Option methods
  function populateOptions(response) {
    options.value = response.data;
    optionPagination.value = response.meta ?? response;
  }

  function addSingleOption(option) {
    options.value.unshift(option.data);
  }

  async function getOptions(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(`/options?per_page=${perPage}&page=${page}&filter=${filter}`, {
      method: "GET",
    });
    return response;
  }

  async function getUnmatchedOptions() {
    const response = await $api("/unmatched/options", { method: "GET" });
    return response.data;
  }

  async function getMatchedOptions() {
    const response = await $api("/matched/options", { method: "GET" });
    return response.data;
  }
  async function getUnlinkedOptions(params = {}) {
    const { perPage = 20, page = 1, filter = "" } = params;
    const response = await $api(
      `/unlinked/options?per_page=${perPage}&page=${page}&filter=${filter}`,
      { method: "GET" },
    );
    return response;
  }

  async function updateOption(slug, data) {
    console.log(slug);
    console.log(data);
    // if (data.checked !== undefined) {
    //   data.checked = !!data.checked;
    // }
    const response = await $api(`/options/${slug}`, {
      method: "PUT",
      body: data,
    });
    return response;
  }

  async function newOption(data) {
    const response = await $api(`/options`, {
      method: "POST",
      body: data,
    });
    return response;
  }

  async function deleteOption(data, force) {
    const response = await $api(`/options/${data.slug}?force=${force}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function deleteUnmatchedOption(data) {
    const response = await $api(`unmatched/options/${data.id}`, {
      method: "DELETE",
      body: data,
    });
    return response;
  }

  async function attachOption(slug_master, data_slave) {
    const response = await $api(`options/${slug_master}/attach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function detachOption(slug_master, data_slave) {
    const response = await $api(`options/${slug_master}/detach`, {
      method: "PUT",
      body: data_slave,
    });
    return response;
  }

  async function mergeOptions(data) {
    const response = await $api(`/merge/options`, {
      method: "POST",
      body: {
        name: data.name,
        options: data.options,
        type: data.type,
        new: data.new,
      },
    });
    return response.data;
  }

  return {
    // Data >> categories
    categories,
    pagination,

    // Data >> boxes
    boxes,
    boxPagination,

    // Data >> options
    options,
    optionPagination,

    // Category methods
    populateCategories,
    addSingleCategory,

    // API methods >> categories
    getCategories,
    getUnmatchedCategories,
    getMatchedCategories,
    getUnlinkedCategories,
    updateCategory,
    deleteCategory,
    deleteUnmatchedCategory,
    newCategory,
    attachCategory,
    detachCategory,
    mergeCategories,

    // box methods
    populateBoxes,
    addSingleBox,

    // API methods >> boxes
    getBoxes,
    getUnmatchedBoxes,
    getMatchedBoxes,
    getUnlinkedBoxes,
    updateBox,
    deleteBox,
    deleteUnmatchedBox,
    newBox,
    attachBox,
    detachBox,
    mergeBoxes,

    // Option methods
    populateOptions,
    addSingleOption,

    // API methods >> options
    getOptions,
    getUnmatchedOptions,
    getMatchedOptions,
    getUnlinkedOptions,
    updateOption,
    deleteOption,
    deleteUnmatchedOption,
    newOption,
    attachOption,
    detachOption,
    mergeOptions,
  };
};

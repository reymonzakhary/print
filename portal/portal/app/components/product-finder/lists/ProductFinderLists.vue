<template>
  <section class="mt-8">
    <div class="flex flex-wrap items-center gap-2">
      <draggable
        v-model="localLists"
        :disabled="!editMode"
        item-key="id"
        class="flex flex-wrap items-center gap-2"
        easing="linear"
        :animation="200"
        :remove-clone-on-hide="true"
        ghost-class="ghost"
        drag-class="drag"
        chosen-class="chosen"
        @end="saveListOrder"
      >
        <template #item="{ element, index }">
          <ProductFinderListsItem
            :list="element"
            :edit-mode="editMode"
            :index="index"
            @update:list="updateList"
            @delete="deleteList"
          />
        </template>
        <template #footer>
          <span v-if="!localLists?.length" class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t("No lists available") }}
          </span>

          <ProductFinderListListsButton
            v-if="editMode || !localLists?.length"
            variant="create"
            @click="showCreateModal = true"
          />

          <ProductFinderListListsButton
            v-if="localLists?.length"
            variant="edit"
            :edit-mode="editMode"
            @click="toggleEditMode"
          />
        </template>
      </draggable>
    </div>

    <ProductFinderListsModalCreate
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @create="createNewList"
    />
  </section>
</template>

<script setup>
const { handleError } = useMessageHandler();

const listsRepository = useListsRepository();
const { data: localLists } = await useLazyAsyncData("lists", () => listsRepository.getLists());

const editMode = ref(false);
const showCreateModal = ref(false);

// Toggle edit mode
const toggleEditMode = async () => {
  try {
    editMode.value = !editMode.value;
    if (!editMode.value) await saveListOrder();
  } catch (error) {
    handleError(error);
  }
};

// Save list order after drag ends
const saveListOrder = async () => {
  listsRepository.reorderLists(localLists.value);
};

// Create a new list
const createNewList = async (newList) => {
  try {
    const createdList = listsRepository.createList(newList);
    localLists.value = [...localLists.value, createdList];
    showCreateModal.value = false;
  } catch (error) {
    handleError(error);
  }
};

// Delete a list
const deleteList = async (index) => {
  try {
    const listId = localLists.value[index].id;
    await listsRepository.deleteList(listId);
    const listsCopy = [...localLists.value];
    listsCopy.splice(index, 1);
    localLists.value = listsCopy;
  } catch (error) {
    handleError(error);
  }
};

// Update a specific list with new data
const updateList = async (updatedList) => {
  try {
    const updatedLists = localLists.value.map((list) =>
      list.id === updatedList.id ? updatedList : list,
    );
    localLists.value = updatedLists;
    await listsRepository.updateList(updatedList.id, updatedList);
  } catch (error) {
    handleError(error);
  }
};
</script>

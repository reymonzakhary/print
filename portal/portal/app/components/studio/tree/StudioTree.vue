<template>
  <div class="h-full overflow-y-auto p-0 font-sans dark:border-gray-900 dark:text-gray-200">
    <StudioTreeSection
      v-for="section in treeConfig"
      :key="section.key"
      :title="section.title"
      :icon="section.icon"
    >
      <template v-for="entity in section.children" :key="entity.key">
        <StudioTreeSection
          v-if="entity.type === 'sub-section'"
          :title="entity.title"
          :icon="entity.icon"
          variant="sub-section"
        >
          <StudioTreeItem
            v-for="entity in entity.children"
            :key="entity.key"
            :disabled="entity.disabled"
            :icon="entity.icon"
            :display-name="entity.displayName"
            :is-active="isEntityActive(entity)"
            @click="navigateTo(entity.route)"
          />
        </StudioTreeSection>
        <StudioTreeItem
          v-else
          :disabled="entity.disabled"
          :icon="entity.icon"
          :display-name="entity.displayName"
          :is-active="isEntityActive(entity)"
          @click="navigateTo(entity.route)"
        />
      </template>
    </StudioTreeSection>
  </div>
</template>

<script setup>
const route = useRoute();

defineProps({
  treeConfig: {
    type: Array,
    required: true,
  },
});

// Determine if an entity is active by comparing routes
const isEntityActive = (entity) => route.path === entity.route;
</script>

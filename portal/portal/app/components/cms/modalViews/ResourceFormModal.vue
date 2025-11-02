<template>
  <ConfirmationModal @on-close="closeModal">
    <template #modal-header>
      {{ $t("Create Resource") }}
    </template>
    <template #modal-body>
      <Form ref="resourceForm">
        <div class="form-section">
          <label for="resourceName">{{ $t("Resource Name") }}</label>
          <UIInputText
            id="resource_name"
            v-model="resourceName"
            name="resourceName"
            placeholder="Resource Name"
            :disabled="!templatesExist"
            :rules="Yup.string().required()"
          />
          <ErrorMessage name="resourceName" as="span" class="text-xs text-red-500" />
        </div>

        <div class="form-section">
          <label for="resourceType">{{ $t("Resource Type") }}</label>
          <ResourceTypeSelector
            :value="resourceType"
            :disabled="locked"
            @input="resourceType = $event"
          />
        </div>

        <div class="form-section">
          <label for="resourceTemplate">{{ $t("Resource Template") }}</label>
          <template v-if="isTemplateFetching || templatesExist">
            <UISelector
              v-model="resourceTemplate"
              name="template"
              :options="templates"
              :is-loading="isTemplateFetching"
              :rules="Yup.string().required()"
            />
            <ErrorMessage name="resourceName" as="span" class="text-xs text-red-500" />
          </template>

          <template v-else-if="permissions.includes('provider-templates-create')">
            <UIButton
              variant="default"
              class="border border-theme-500 hover:border-theme-100"
              :icon="['fas', 'plus']"
              @click="handleCreateTemplateClick"
            >
              {{ $t("Create Template") }}
            </UIButton>
          </template>
          <template v-else>
            <p class="text-sm text-gray-500">
              {{ $t("Please request your administrator to create a template.") }}
            </p>
          </template>
        </div>

        <div v-if="templatesExist" class="form-section">
          <label for="resourceParent">{{ $t("Resource Parent") }}</label>
          <UISelector
            v-model="resourceParent"
            name="resourceParent"
            :options="resources"
            :disabled="!templatesExist || resources.length === 0"
          />
        </div>
      </Form>
    </template>

    <template #confirm-button>
      <ModalButton
        variant="success"
        :disabled="!everythingFilledIn"
        @click.once="submitResourceForm"
      >
        {{ $t("Create Resource") }}
      </ModalButton>
    </template>
  </ConfirmationModal>
</template>

<script>
import * as Yup from "yup";

export default {
  props: {
    forceNoTemplates: {
      type: Boolean,
      default: false,
    },
    forceNoResources: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["onCreateResource", "close-modal"],
  setup() {
    const { permissions } = storeToRefs(useAuthStore());
    const api = useAPI();
    return { permissions, api };
  },
  data() {
    return {
      resourceName: "",
      //
      resourceType: "1",
      //
      isTemplateFetching: true,
      templates: [],
      resourceTemplate: "0",
      //
      isResourceFetching: true,
      resources: [],
      resourceParent: "0",
      //
      isCreatingResource: false,
      //
      Yup,
    };
  },
  computed: {
    everythingFilledIn() {
      return this.resourceName !== "" && this.resourceTemplate !== "0";
    },
    templatesExist() {
      return this.templates.length > 0;
    },
  },
  async created() {
    try {
      const templateResponse = await this.api.get("/modules/cms/templates");
      if (!this.forceNoTemplates) {
        const normalizedTemplates = this.normalizeTemplates(templateResponse.data);
        this.templates = normalizedTemplates;
      }
      this.isTemplateFetching = false;

      if (this.templates.length > 0) {
        const resourceResponse = await this.api.get("/modules/cms/tree");
        if (!this.forceNoResources) {
          const normalizedResources = this.normalizeResources(resourceResponse.data);
          this.resources = normalizedResources;
        }
        this.isResourceFetching = false;
      }
    } catch (err) {
      this.handleError(err);
    }
  },
  methods: {
    handleCreateTemplateClick() {
      this.closeModal();
      navigateTo("/manage/cms/templates");
    },
    constructResource() {
      return {
        title: this.resourceName,
        template_id: this.resourceTemplate === "0" ? null : this.resourceTemplate,
        parent_id: this.resourceParent === "0" ? null : this.resourceParent,
        isfolder: false,
        resource_type_id: this.resourceType,
      };
    },
    createResource() {
      this.isCreatingResource = true;
      const resourceData = this.constructResource();
      this.$emit("onCreateResource", resourceData);
    },
    submitResourceForm() {
      this.$refs.resourceForm.validate().then((success) => {
        if (success) {
          this.createResource();
        }
      });
    },
    closeModal() {
      this.$emit("close-modal");
    },
    normalizeTemplates(templates) {
      return templates.map((template) => {
        return {
          label: template.name,
          value: template.id,
        };
      });
    },
    normalizeResources(resources) {
      return resources.map((resource) => {
        return {
          label: resource.title,
          value: resource.id,
        };
      });
    },
  },
};
</script>

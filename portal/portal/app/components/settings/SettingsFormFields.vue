<template>
  <div
    :key="setting.name"
    class="flex items-center border-b p-4 py-4 last:border-0 dark:border-gray-900"
  >
    <div class="w-1/2">
      <h3 class="text-xs font-bold uppercase tracking-wide">
        {{ setting.name }}
      </h3>
      <p v-if="setting.description" class="text-sm text-gray-500">
        {{ setting.description }}
      </p>
      <p v-else class="text-sm italic text-gray-500">
        {{ $t("No description available") }}
      </p>
    </div>

    <div class="w-1/2">
      <template v-if="setting.data_type === 'string' && setting.secure_variable === 1">
        <UIInputText
          :id="setting.name + '_confirm'"
          v-model="validate"
          :disabled="!permissions.includes('settings-update')"
          :name="setting.name + '_confirm'"
          placeholder="type new password"
          type="text"
        />
        <span class="text-sm text-gray-500"> {{ $t("validate") }} {{ setting.name }} </span>
      </template>

      <!-- text input -->
      <UIInputText
        v-if="setting.data_type === 'string'"
        :id="setting.name"
        v-model="setting.value"
        :disabled="!permissions.includes('settings-update')"
        :name="setting.name"
        type="text"
        @blur="updateSetting(setting)"
      />
      <!-- setting.secure_variable
									? updateSetting(setting)
									: setting.secure_variable &&
									  validate === setting.value
									? (updateSetting(setting), (validate = ''))
									: set_toast({
											text: 'passwords don\'t match',
											status: 'red'
									  })-->
      <!-- number input -->
      <UIInputText
        v-if="setting.data_type === 'integer'"
        :id="setting.name"
        v-model="setting.value"
        :name="setting.name"
        :step="setting.incremental"
        :disabled="!permissions.includes('settings-update')"
        type="number"
        @blur="updateSetting(setting)"
      />

      <UploadImage
        v-if="setting.data_type === 'image'"
        v-model="setting.value"
        :disabled="!permissions.includes('settings-update')"
        :image="setting.value"
        @update-setting-logo="updateSetting(setting, $event)"
      />

      <!-- single & multiple select -->
      <UIVSelect
        v-if="setting.data_type === 'array' && setting.data_variable.length > 3"
        :model-value="setting.value"
        :multiple="setting.multi_select"
        :options="setting.data_variable"
        :disabled="!permissions.includes('settings-update')"
        class="input w-full rounded-l bg-white !p-0 text-sm"
        @update:model-value="updateSetting(setting, $event)"
      />

      <fieldset
        v-if="
          setting.data_type === 'array' &&
          setting.data_variable.length <= 3 &&
          setting.multi_select === (0 || false)
        "
        :disabled="!permissions.includes('settings-update')"
        class="flex flex-col text-sm"
      >
        <span v-for="(v, i) in setting.data_variable" :key="v" class="mr-2">
          <input
            :id="v"
            v-model="setting.value"
            :disabled="!permissions.includes('settings-update')"
            type="radio"
            :name="setting.name"
            :value="v"
            :checked="v === setting.value"
            @change="updateSetting(setting)"
          />
          <label :for="v">{{ v }}</label>
        </span>
      </fieldset>

      <fieldset
        v-if="
          setting.data_type === 'array' &&
          setting.data_variable.length <= 3 &&
          setting.multi_select === (1 || true)
        "
        class="flex flex-col text-sm"
        :disabled="!permissions.includes('settings-update')"
      >
        <span v-for="v in setting.data_variable" :key="v" class="mr-2">
          <input
            :id="v"
            type="checkbox"
            :disabled="!permissions.includes('settings-update')"
            :name="setting.name"
            :value="v"
            :checked="setting.value.includes(v)"
            @change="updateSetting(setting, v)"
          />
          <label :for="v">{{ v }}</label>
        </span>
      </fieldset>

      <!-- Boolean switch -->
      <div
        v-if="setting.data_type === 'boolean'"
        class="relative my-1 flex items-center justify-between text-sm"
      >
        <p>
          {{ setting.name }}
        </p>

        <div
          class="relative mx-2 mr-4 h-4 w-10 cursor-pointer rounded-full transition duration-200 ease-linear"
          :class="[setting.value === true ? 'bg-theme-400' : 'bg-gray-400']"
        >
          <label
            :for="setting.name"
            class="absolute left-0 mb-2 h-4 w-4 transform cursor-pointer rounded-full border-2 bg-white transition duration-100 ease-linear"
            :class="[
              setting.value === true
                ? 'translate-x-6 border-theme-500'
                : 'translate-x-0 border-gray-400',
            ]"
          />
          <input
            :id="setting.name"
            v-model="setting.value"
            :disabled="!permissions.includes('settings-update')"
            :name="setting.name"
            type="checkbox"
            class="h-full w-full appearance-none focus:outline-none active:outline-none"
            :checked="setting.value === true"
            @change="updateSetting(setting)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapMutations, mapActions } from "vuex";
export default {
  props: {
    setting: {
      type: Object,
      required: true,
    },
    i: {
      type: Number,
      required: true,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  setup() {
    const api = useAPI();
    const { handleError, handleSuccess } = useMessageHandler();
    const { addToast } = useToastStore();
    const { permissions } = storeToRefs(useAuthStore());
    return { api, handleError, handleSuccess, addToast, permissions };
  },
  methods: {
    ...mapMutations({
      set_namespace: "settings/set_namespace",
      update_setting: "settings/update_setting",
      set_show_reload: "settings/set_show_reload",
    }),
    ...mapActions({
      get_settings: "settings/get_settings",
    }),
    updateSetting(setting, value = null) {
      if (setting.data_type === "image") {
        value = value.path;
      }
      this.api
        .put(`settings/${setting.key}`, {
          namespace: setting.namespace,
          area: setting.area,
          value: value ? value : setting.value,
          multi_select: setting.multi_select,
        })
        .then(async (response) => {
          this.addToast({
            type: "success",
            message: this.$t("Setting updated"),
          });
          this.update_setting({
            key: setting.key,
            value: response.data.value ? response.data.value : setting.value,
          });
          if (setting.key === "manager_language") {
            const { $i18n: i18n } = useNuxtApp();
            i18n.setLocale(value.toLowerCase());
          }
          this.set_show_reload(true);
        })
        .catch((error) => {
          this.handleError(error);
        });
    },
  },
};
</script>

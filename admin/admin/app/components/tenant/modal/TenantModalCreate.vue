<template>
  <ConfirmationModal classes="w-auto max-w-7xl mx-5" @on-close="closeModal">
    <template #modal-header>
      <template v-if="!props.editing"> Create Tenant </template>
      <template v-else> Edit Tenant </template>
    </template>

    <template #modal-body>
      <Form
        ref="createUserForm"
        :validation-schema="TenantSchema"
        class="flex flex-col gap-2"
        autocomplete="off"
        @submit="handleSubmit"
      >
        <fieldset class="grid grid-cols-2 gap-4 p-4 rounded border">
          <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Personal Information</legend>

          <div class="col-span-2">
            <section v-if="props.editing" class="mb-8 flex">
              <div class="relative">
                <img :src="tenant.logo" alt="logo" class="object-contain h-20 rounded border" />
                <button
                  type="button"
                  class="absolute -top-2 -right-2 px-1 text-gray-500 bg-white border rounded-full shadow-sm hover:bg-gray-100"
                  @click="changeLogo = !changeLogo"
                >
                  <font-awesome-icon
                    :icon="['fal', !changeLogo ? 'pen' : 'circle-xmark']"
                    class="w-4 h-4 text-gray-500 dark:text-gray-100"
                  />
                </button>
              </div>
              <div v-if="changeLogo" class="flex flex-col gap-2 ml-4">
                <div
                  class="flex justify-center items-center p-4 w-full h-32 rounded-lg border-2 border-dashed cursor-pointer hover:border-blue-500 focus:border-blue-500"
                  @dragover.prevent
                  @dragenter.prevent
                  @drop.prevent="handleFileDrop"
                >
                  <input
                    id="file-upload"
                    ref="fileInput"
                    type="file"
                    class="hidden"
                    @change="handleFileChange"
                  />
                  <button
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none"
                    @click="triggerFileInput"
                  >
                    Browse File
                  </button>
                  <div class="ml-4 text-gray-500">
                    <span v-if="!tenant.file">or drag and drop a file here</span>
                    <div v-else class="flex gap-2 items-center">
                      <span class="text-gray-700">
                        {{ tenant.file.name }} ({{ formatFileSize(tenant.file.size) }})
                      </span>
                      <UIButton
                        variant="link"
                        class="text-xs !text-red-500 underline hover:text-red-600 hover:!bg-red-100"
                        @click="clearFile"
                      >
                        Remove
                      </UIButton>
                    </div>
                  </div>
                </div>
                <ErrorMessage name="logo" as="span" class="text-xs text-red-500" />
              </div>
            </section>

            <label
              for="gender"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              Salutation
            </label>
            <div class="flex">
              <label
                for="male"
                class="flex items-center mr-2 text-sm capitalize default checked:font-bold checked:text-theme-500"
                :class="{
                  'font-bold text-theme-500': tenant.gender === 'male',
                }"
              >
                <input
                  id="male"
                  v-model="tenant.gender"
                  type="radio"
                  name="gender"
                  value="male"
                  class="mr-1"
                  checked
                />
                Mr.
              </label>
              <label
                for="female"
                class="flex items-center mr-2 text-sm capitalize default"
                :class="{
                  'font-bold text-theme-500': tenant.gender === 'female',
                }"
              >
                <input
                  id="female"
                  v-model="tenant.gender"
                  type="radio"
                  name="gender"
                  value="female"
                  class="mr-1"
                />
                Ms.
              </label>
              <label
                for="other"
                class="flex items-center mr-2 text-sm capitalize default"
                :class="{
                  'font-bold text-theme-500': tenant.gender === 'other',
                }"
              >
                <input
                  id="other"
                  v-model="tenant.gender"
                  type="radio"
                  name="gender"
                  value="other"
                  class="mr-1"
                />
                Other.
              </label>
            </div>
            <ErrorMessage name="gender" as="span" class="text-xs text-red-500" />
          </div>
          <div>
            <label
              for="firstName"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              First name:
            </label>
            <UIInputText
              v-model="tenant.firstName"
              name="firstName"
              placeholder=""
              required
              autocomplete="off"
            />
            <ErrorMessage name="firstName" as="span" class="text-xs text-red-500" />
          </div>
          <div>
            <label
              for="lastName"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              Last name:
            </label>
            <UIInputText
              v-model="tenant.lastName"
              name="lastName"
              placeholder=""
              required
              autocomplete="off"
            />
            <ErrorMessage name="lastName" as="span" class="text-xs text-red-500" />
          </div>
        </fieldset>

        <fieldset v-if="!props.editing" class="flex flex-col gap-4 p-4 rounded border">
          <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">System Information</legend>
          <div class="col-span-2">
            <label
              for="companyName"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              Company Name:
            </label>
            <UIInputText
              v-model="tenant.companyName"
              name="companyName"
              placeholder=""
              required
              autocomplete="off"
              @input="
                !props.editing
                  ? (tenant.fqdn = tenant.companyName.toLowerCase().replace(/[^a-z0-9]/g, '-'))
                  : null
              "
            />
            <ErrorMessage name="companyName" as="span" class="text-xs text-red-500" />
          </div>
          <div>
            <label
              for="fqdn"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              URL Prefix:
            </label>
            <UIInputText
              v-model="tenant.fqdn"
              prefix="https://"
              affix=".prindustry.com"
              name="fqdn"
              placeholder=""
              required
            />
            <ErrorMessage name="fqdn" as="span" class="text-xs text-red-500" />
          </div>

          <div v-if="!props.editing" class="grid grid-cols-2 gap-4">
            <div>
              <label
                for="email"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Email:
              </label>
              <UIInputText
                v-model="tenant.email"
                name="email"
                type="email"
                placeholder=""
                required
                autocomplete="off"
              />
              <ErrorMessage name="email" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="password"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Password:
              </label>
              <UIInputText
                v-model="tenant.password"
                name="password"
                type="password"
                placeholder=""
                required
                autocomplete="off"
              />
              <ErrorMessage name="password" as="span" class="text-xs text-red-500" />
            </div>
          </div>

          <div>
            <label
              for="file-upload"
              class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
            >
              Upload File
            </label>
            <div
              class="flex justify-center items-center p-4 w-full h-32 rounded-lg border-2 border-dashed cursor-pointer hover:border-blue-500 focus:border-blue-500"
              @dragover.prevent
              @dragenter.prevent
              @drop.prevent="handleFileDrop"
            >
              <input
                id="file-upload"
                ref="fileInput"
                type="file"
                class="hidden"
                @change="handleFileChange"
              />
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded hover:bg-blue-600 focus:outline-none"
                @click="triggerFileInput"
              >
                Browse File
              </button>
              <div class="ml-4 text-gray-500">
                <span v-if="!tenant.file">or drag and drop a file here</span>
                <div v-else class="flex gap-2 items-center">
                  <span class="text-gray-700">
                    {{ tenant.file.name }} ({{ formatFileSize(tenant.file.size) }})
                  </span>
                  <UIButton
                    variant="link"
                    class="text-xs !text-red-500 underline hover:text-red-600 hover:!bg-red-100"
                    @click="clearFile"
                  >
                    Remove
                  </UIButton>
                </div>
              </div>
            </div>
            <ErrorMessage name="logo" as="span" class="text-xs text-red-500" />
          </div>
        </fieldset>
      </Form>
    </template>

    <template #confirm-button>
      <UIModalButton variant="success" :disabled="loading" @click="handleSubmit">
        <template v-if="!props.editing"> Create Tenant </template>
        <template v-else> Update Tenant </template>
      </UIModalButton>
    </template>
  </ConfirmationModal>
</template>

<script setup>
import * as yup from "yup";

const props = defineProps({
  editing: {
    type: [Object, null],
    default: () => null,
  },
});
const emit = defineEmits(["on-close", "on-tenant-created"]);

const TenantSchema = yup.object({
  gender: yup.string(),
  firstName: yup.string().required(),
  lastName: yup.string().required(),
  companyName: yup.string().required(),
  fqdn: yup.string().required(),
  email: !props.editing ? yup.string().email().required() : yup.string().email(),
  password: !props.editing ? yup.string().required() : yup.string(),
  logo: yup.mixed(),
});

const tenantRepository = useTenantRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();

const loading = ref(false);
const createUserForm = ref(null);
const fileInput = ref(null);
const changeLogo = ref(false);

const tenant = ref({
  gender: "male",
  firstName: "",
  lastName: "",
  companyName: "",
  fqdn: "",
  email: "",
  password: "",
  file: null,
});

onMounted(() => {
  if (props.editing) {
    tenant.value.gender = props.editing.owner.gender;
    tenant.value.firstName = props.editing.owner.name.split(" ")[0];
    tenant.value.lastName = props.editing.owner.name.split(" ")[1];
    tenant.value.companyName = props.editing.name;
    tenant.value.logo = props.editing.logo;
    tenant.value.fqdn = props.editing.domain.split(".")[0];
  }
});

async function handleSubmit() {
  try {
    loading.value = true;
    if (props.editing) {
      await updateTenant();
    } else {
      await createTenant();
    }
    emit("on-tenant-created");
    emit("on-close");
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function updateTenant() {
  const data = new FormData();
  data.append("gender", tenant.value.gender);
  data.append("first_name", tenant.value.firstName);
  data.append("last_name", tenant.value.lastName);
  data.append("company_name", tenant.value.companyName);
  data.append("fqdn", tenant.value.fqdn);
  if (tenant.value.file) {
    data.append("logo", tenant.value.file);
  }
  await tenantRepository.updateTenant(props.editing.id, data);
  addToast({
    message: "Tenant updated successfully",
    type: "success",
  });
}

async function createTenant() {
  const data = new FormData();
  data.append("gender", tenant.value.gender);
  data.append("first_name", tenant.value.firstName);
  data.append("last_name", tenant.value.lastName);
  data.append("company_name", tenant.value.companyName);
  data.append("fqdn", tenant.value.fqdn);
  data.append("email", tenant.value.email);
  data.append("password", tenant.value.password);
  if (tenant.value.file) {
    data.append("logo", tenant.value.file);
  }

  await tenantRepository.createTenant(data);
  addToast({
    message: "Creating tenant. You will be notified once the tenant has been successfully created.",
    type: "info",
  });
}

function closeModal() {
  tenant.value.file = null;
  emit("on-close");
}

const triggerFileInput = () => fileInput.value.click();
const handleFileChange = (e) => e.target.files[0] && (tenant.value.file = e.target.files[0]);
const handleFileDrop = (e) =>
  e.dataTransfer.files[0] && (tenant.value.file = e.dataTransfer.files[0]);
function clearFile() {
  tenant.value.file = null;
  fileInput.value.value = null;
}
function formatFileSize(size) {
  const i = size === 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024));
  return (size / Math.pow(1024, i)).toFixed(2) * 1 + " " + ["B", "KB", "MB", "GB", "TB"][i];
}
</script>

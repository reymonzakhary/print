<template>
  <ConfirmationModal classes="w-auto max-w-7xl mx-5" @on-close="closeModal">
    <template #modal-header>
      <template v-if="props.editing"> Edit User </template>
      <template v-else> Create User </template>
    </template>

    <template #modal-body>
      <Form
        ref="createUserForm"
        :validation-schema="TenantSchema"
        class="flex flex-col gap-4"
        autocomplete="off"
        @submit="handleSubmit"
      >
        <div
          class="grid gap-4"
          :class="{ 'grid-cols-2': !props.editing, 'grid-cols-1': props.editing }"
        >
          <fieldset class="grid grid-cols-2 col-span-1 gap-4 p-4 rounded border">
            <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">General Information</legend>
            <div class="col-span-2">
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
            <div :class="{ 'col-span-2': props.editing }">
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
            <div v-if="!props.editing">
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
          </fieldset>
          <details v-if="props.editing">
            <summary>Delete User</summary>
            <fieldset class="flex flex-col col-span-1 gap-4 p-4 max-w-md rounded border">
              <p>Deleting this user will permanently remove all data associated with this user.</p>
              <div class="grid grid-cols-3 gap-4 mt-auto">
                <label class="flex col-span-2 items-start">
                  <input v-model="confirmDelete" type="checkbox" class="mt-1 mr-2" />
                  I understand that deleting this user is permanent and cannot be undone.
                </label>
                <div class="flex flex-col col-span-1 justify-end">
                  <UIModalButton
                    variant="danger"
                    :disabled="!confirmDelete || loading"
                    @click.prevent="deleteUser"
                  >
                    Delete User
                  </UIModalButton>
                </div>
              </div>
            </fieldset>
          </details>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <fieldset v-if="!props.editing" class="grid grid-cols-2 gap-4 p-4 rounded border">
            <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Company Info</legend>
            <!-- TODO: Add Company URL -->
            <div class="col-span-2">
              <label
                for="companyName"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Company name:
              </label>
              <UIInputText
                v-model="company.name"
                name="companyName"
                placeholder=""
                required
                autocomplete="off"
              />
              <ErrorMessage name="companyName" as="span" class="text-xs text-red-500" />
            </div>
            <div class="col-span-2">
              <label
                for="companyDescription"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Company description:
              </label>
              <UIInputText
                v-model="company.description"
                name="companyDescription"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="companyDescription" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="taxNumber"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Tax Nr:
              </label>
              <UIInputText
                v-model="company.taxNumber"
                name="companyTaxNumber"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="taxNumber" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="chamberOfCommerce"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Chamber of Commerce:
              </label>
              <UIInputText
                v-model="company.chamberOfCommerce"
                name="companyChamberOfCommerce"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="chamberOfCommerce" as="span" class="text-xs text-red-500" />
            </div>
            <div :class="{ 'col-span-2': props.editing }">
              <label
                for="callback"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                URL:
              </label>
              <UIInputText v-model="company.url" name="url" placeholder="" autocomplete="off" />
              <ErrorMessage name="url" as="span" class="text-xs text-red-500" />
            </div>
            <div v-if="!props.editing">
              <label
                for="authorization"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Authorization:
              </label>
              <UISelector
                v-model="company.authorization"
                name="authorization"
                :options="[
                  { value: 'Bearer', label: 'Bearer' },
                  { value: 'password', label: 'Password' },
                ]"
              />
              <ErrorMessage name="authorization" as="span" class="text-xs text-red-500" />
            </div>
          </fieldset>

          <fieldset v-if="!props.editing" class="grid grid-cols-2 gap-4 p-4 rounded border">
            <legend class="px-3 pb-1 mt-1 text-sm font-bold uppercase">Company Address</legend>
            <div>
              <label
                for="country"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Country:
              </label>
              <UICountrySelector
                v-model="address.country"
                name="country"
                class="max-w-48"
                required
              />
              <ErrorMessage name="country" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="street"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Street:
              </label>
              <UIInputText
                v-model="address.street"
                name="street"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="street" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="number"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Nr.:
              </label>
              <UIInputText
                v-model="address.number"
                name="number"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="number" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="zipcode"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                Zipcode:
              </label>
              <UIInputText
                v-model="address.zipcode"
                name="zipcode"
                placeholder=""
                autocomplete="off"
              />
              <ErrorMessage name="zipcode" as="span" class="text-xs text-red-500" />
            </div>
            <div>
              <label
                for="city"
                class="block mb-1 text-xs font-bold tracking-wide text-gray-500 uppercase"
              >
                City:
              </label>
              <UIInputText v-model="address.city" name="city" placeholder="" autocomplete="off" />
              <ErrorMessage name="city" as="span" class="text-xs text-red-500" />
            </div>
            <!-- TODO: Add Address Region -->
          </fieldset>
        </div>
      </Form>
    </template>

    <template #confirm-button>
      <UIModalButton
        variant="success"
        :disabled="loading"
        @click="createUserForm.$el.requestSubmit()"
      >
        <template v-if="props.editing"> Update User </template>
        <template v-else> Create User </template>
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
const emit = defineEmits(["on-close", "on-user-created", "on-user-updated", "on-user-deleted"]);

const TenantSchema = yup.object({
  gender: yup.string(),
  firstName: yup.string().required("First name is required"),
  lastName: yup.string().required("Last name is required"),
  email: yup.string().email().required(),
  password: !props.editing ? yup.string().required() : yup.string(),
  companyName: yup.string().required("Company name is required"),
  companyDescription: yup.string().required("Company description is required"),
  companyTaxNumber: yup.string().required("Tax number is required"),
  companyChamberOfCommerce: yup.string().required("Chamber of Commerce number is required"),
  url: yup.string().required(),
  authorization: !props.editing
    ? yup.string().required("Authorization method is required")
    : yup.string(),
  country: yup.string().required("Country is required"),
  street: yup.string().required("Street is required"),
  number: yup.string().required("Street number is required"),
  zipcode: yup.string().required("Zipcode is required"),
  city: yup.string().required("City is required"),
});

const userRepository = useUserRepository();
const { handleError } = useMessageHandler();
const { addToast } = useToastStore();
const { confirm } = useConfirmation();

const confirmDelete = ref(false);
const loading = ref(false);
const createUserForm = ref(null);

const tenant = ref({
  gender: "male",
  firstName: "",
  lastName: "",
  email: "",
  password: "",
});
const company = ref({
  name: "",
  description: "",
  taxNumber: "",
  chamberOfCommerce: "",
  callback: "",
  authorization: "",
});
const address = ref({
  country: "",
  street: "",
  number: "",
  zipcode: "",
  city: "",
});

onMounted(() => {
  if (props.editing) {
    tenant.value.gender = props.editing.gender;
    tenant.value.firstName = props.editing.firstName;
    tenant.value.lastName = props.editing.lastName;
    tenant.value.email = props.editing.email;
    company.value.name = props.editing.company.name;
    company.value.description = props.editing.company.description;
    company.value.taxNumber = props.editing.company.taxNumber;
    company.value.chamberOfCommerce = props.editing.company.chamberOfCommerce;
    company.value.url = props.editing.company.url;
    if (props.editing.authorization) {
      company.value.authorization = props.editing.authorization;
    }
    address.value.country = props.editing.address.country;
    address.value.street = props.editing.address.street;
    address.value.number = props.editing.address.number;
    address.value.zipcode = props.editing.address.zipcode;
    address.value.city = props.editing.address.city;
  }
});

async function handleSubmit() {
  try {
    loading.value = true;
    if (props.editing) {
      await updateUser();
      emit("on-user-updated");
    } else {
      await createUser();
      emit("on-user-created");
    }
    emit("on-close");
  } catch (error) {
    handleError(error);
  } finally {
    loading.value = false;
  }
}

async function updateUser() {
  const data = {
    ...tenant.value,
    company: company.value,
    address: address.value,
  };
  if (props.editing?.email === tenant.value.email) {
    delete data.email;
  }
  await userRepository.updateUser(props.editing.id, data);
  addToast({
    message: "User updated successfully",
    type: "success",
  });
}

async function createUser() {
  const data = {
    ...tenant.value,
    company: company.value,
    address: address.value,
  };
  await userRepository.createUser(data);
  addToast({
    message: "User created successfully",
    type: "success",
  });
}

async function deleteUser() {
  try {
    loading.value = true;
    await confirm({
      title: "Delete User",
      message: "Are you sure you want to delete this user?",
      confirmOptions: {
        label: "Delete",
        variant: "danger",
      },
    });
    await userRepository.deleteUser(props.editing.id);
    addToast({
      message: "User deleted successfully",
      type: "success",
    });
    emit("on-user-deleted");
    emit("on-close");
  } catch (error) {
    if (error.cancelled) return;
    handleError(error);
  } finally {
    loading.value = false;
  }
}

function closeModal() {
  tenant.value.file = null;
  emit("on-close");
}
</script>

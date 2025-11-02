<template>
  <ConfirmationModal classes="w-auto">
    <template #modal-header>
      <h2 class="block font-bold tracking-wide uppercase">
        <font-awesome-icon class="mr-1" :icon="['fad', 'user-plus']" />
        {{ $t("new") }} {{ $t(type) }}
      </h2>
    </template>

    <template #modal-body>
      <article
        v-if="!created"
        class="flex flex-wrap items-stretch justify-between w-full h-full"
      >
        <section class="flex flex-col flex-wrap w-full p-4 lg:w-1/3">
          <h2 class="block font-bold tracking-wide uppercase">
            {{ $t("account") }}
          </h2>
          <!-- EMAIL -->
          <div class="py-4">
            <span class="flex items-center justify-between">
              <label
                for="email"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("we need an email") }} ...
              </label>
              <span class="relative w-1/2">
                <input
                  ref="email"
                  v-model="email"
                  type="email"
                  class="relative z-10 w-full py-1 pl-8 pr-2 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                  placeholder="email"
                  :class="{ 'border-orange-500': emailExist }"
                  @keyup="validateEmail"
                />
                <font-awesome-icon
                  class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
                  :icon="['fal', 'envelope']"
                />
                <span
                  v-if="required"
                  class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                >
                  {{ $t("required") }}
                </span>
              </span>
            </span>
            <transition name="slide">
              <div
                v-show="emailExist"
                class="relative z-0 px-2 pt-2 pb-1 -mt-1 text-center text-white bg-orange-500 rounded"
              >
                {{ $t("email exists") }}
              </div>
            </transition>
          </div>
          <!-- PASSWORD -->
          <div class="py-4 border-b dark:border-gray-900">
            <transition name="fade">
              <span class="flex items-center justify-between">
                <label
                  for="password"
                  class="block w-1/2 text-xs font-bold tracking-wide uppercase"
                >
                  {{ $t("password") }}
                </label>
                <div class="relative flex w-1/2">
                  <span v-if="enterpassword">
                    <input
                      ref="password"
                      v-model="password"
                      :type="!view ? 'password' : 'text'"
                      class="w-full px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                      placeholder="****"
                    />
                    <font-awesome-icon
                      class="absolute right-0 mt-2 mr-2 text-gray-600 rounded-full cursor-pointer hover:bg-gray-300"
                      :icon="['fal', !view ? 'eye-slash' : 'eye']"
                      @click="view = !view"
                    />
                  </span>
                  <span
                    v-if="required"
                    class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                  >
                    {{ $t("required") }}
                  </span>
                  <div
                    v-if="!enterpassword"
                    class="text-sm italic text-gray-500"
                  >
                    <font-awesome-icon
                      class="text-theme-500"
                      :icon="['fal', 'circle-info']"
                    />
                    Password is generated and sent to customers email
                  </div>
                </div>
              </span>
            </transition>
            <div
              v-if="type === 'user'"
              class="w-full mt-4 text-right text-theme-900"
            >
              <button
                class="text-theme-500"
                @click="enterpassword = !enterpassword"
              >
                {{
                  !enterpassword
                    ? "Manually enter password"
                    : "Generate password"
                }}
              </button>
            </div>
          </div>

          <!-- SALUTATION -->
          <div class="pt-4">
            <span class="flex items-center justify-between">
              <label
                for="gender"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("salutation") }}
              </label>
              <div class="w-1/2">
                <input
                  id="male"
                  v-model="gender"
                  type="radio"
                  name="gender"
                  value="male"
                />
                <label
                  for="male"
                  class="mr-2 text-sm"
                  :class="{
                    'font-bold text-theme-500': gender === 'male',
                  }"
                >
                  {{ $t("mr") }}
                </label>
                <input
                  id="female"
                  v-model="gender"
                  type="radio"
                  name="gender"
                  value="female"
                />
                <label
                  for="female"
                  class="mr-2 text-sm"
                  :class="{
                    'font-bold text-theme-500': gender === 'female',
                  }"
                >
                  {{ $t("ms") }}
                </label>
                <input
                  id="other"
                  v-model="gender"
                  type="radio"
                  name="gender"
                  value="other"
                />
                <label
                  for="other"
                  class="mr-2 text-sm"
                  :class="{
                    'font-bold text-theme-500': gender === 'other',
                  }"
                >
                  {{ $t("other") }}
                </label>
              </div>
            </span>
          </div>
          <!-- NAME -->
          <div class="">
            <span class="flex items-center justify-between">
              <label
                for="first_name"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("first name") }}
              </label>
              <span class="relative w-1/2">
                <input
                  v-model="firstName"
                  type="text"
                  class="relative z-10 w-full max-w-xs px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                  placeholder="First Name"
                  required
                />
                <span
                  v-if="required"
                  class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                >
                  {{ $t("required") }}
                </span>
              </span>
            </span>
          </div>

          <!-- Middle Name -->
          <!-- <div class="flex items-center justify-between">
						<label
							for="middle_name"
							class="block w-1/2 text-xs font-bold tracking-wide uppercase"
						>
							{{ $t("middle name") }}
						</label>
						<input
							type="text"
							class="relative z-10 w-full max-w-xs px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
							v-model="middleName"
						/>
					</div> -->

          <!-- Last Name -->
          <div
            class="py-4"
            :class="{
              'border-b dark:border-gray-900': type === 'customer',
            }"
          >
            <span class="flex items-center justify-between">
              <label
                for="last_name"
                class="block w-1/2 text-xs font-bold tracking-wide uppercase"
              >
                {{ $t("last name") }}
              </label>
              <div class="relative w-1/2">
                <input
                  v-model="lastName"
                  type="text"
                  class="relative z-10 w-full max-w-xs px-2 py-1 bg-white border rounded dark:border-gray-900 dark:bg-gray-700 focus:outline-none focus:ring focus:border-theme-300"
                  placeholder="Last Name"
                  required
                />
                <span
                  v-if="required"
                  class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
                >
                  {{ $t("required") }}
                </span>
              </div>
            </span>
          </div>
        </section>

        <!-- ROLES -->
        <div
          class="w-full h-full p-2 py-4 rounded lg:w-1/3 bg-gray-50 dark:border-gray-900"
        >
          <h2 class="block font-bold tracking-wide uppercase">
            {{ $t("what roles are connected") }}
          </h2>
          <ul class="py-4">
            <li
              v-for="(role, idx) in roles"
              :key="'user_role_' + idx"
              class="flex items-center justify-between p-2 group"
            >
              <div class="flex items-center justify-between">
                <ValueSwitch
                  :name="$display_name(role.display_name)"
                  :set-checked="selectedRoles.includes(role.name)"
                  classes="justify-between font-normal w-full"
                  @checked-value="updateUserRoles(role)"
                />
              </div>
            </li>
          </ul>
          <!-- <font-awesome-icon
									class="absolute top-0 left-0 z-10 mt-2 ml-2 text-gray-500"
									:icon="['fal', 'key']"
								/> -->
          <span
            v-if="required"
            class="absolute ml-2 text-xs text-orange-500 -left-2 -top-4"
          >
            {{ $t("required") }}
          </span>
        </div>

        <div class="w-full p-2 py-4 lg:w-1/3">
          <h2 class="block font-bold tracking-wide uppercase">
            {{ $t("address") }}
          </h2>
          <NewAdressContent
            v-if="type === 'customer'"
            :user_id="activeUserId"
            :first="true"
            :extended_fields="false"
            :type="type"
            :required="required"
          ></NewAdressContent>
        </div>
      </article>

      <div v-if="created" class="p-4">
        <div class="text-lg font-bold">
          {{ $t("user created succesfully") }}
        </div>
        <img
          :src="user.avatar"
          :alt="user.username"
          class="my-4 border rounded-full"
        />
        <div class="font-bold">
          <span class="text-gray-500"> #{{ user.id }} </span>
          {{ salutation }}
          {{ profile.first_name }}
          {{ profile.last_name }}
        </div>
        <div>
          <font-awesome-icon class="mr-1" :icon="['fal', 'envelope']" />
          {{ user.email }}
        </div>

        <hr class="w-full my-4 border" />

        <span v-if="address.length > 0">
          <div class="capitalize">
            <font-awesome-icon class="mr-1" :icon="['fal', 'map-location-dot']" />
            {{ address[0].address }} {{ address[0].number }}
          </div>
          <div>
            <font-awesome-icon class="mr-1" :icon="['fal', 'city']" />
            {{ address[0].city }} {{ address[0].zip_code }}
          </div>
          <div v-if="address[0].region">
            <font-awesome-icon class="mr-1" :icon="['fal', 'earth-europe']" />
            {{ address[0].region }}
          </div>
        </span>
      </div>
    </template>

    <template #confirm-button>
      <button
        v-show="!userExist && !emailExist && !created"
        class="px-2 py-1 mx-1 ml-auto text-sm text-white transition-colors duration-150 bg-green-500 rounded-full hover:bg-green-600"
        @click="createUser()"
      >
        <font-awesome-icon class="fa-sm" :icon="['fal', 'right']" />
        {{ $t("create") }} {{ type }}
      </button>
      <button
        v-if="created"
        class="w-1/4 px-2 py-1 mx-1 ml-auto text-sm text-white transition-colors duration-150 bg-green-500 rounded-full hover:bg-green-600"
        @click="set_modal_name('')"
      >
        <font-awesome-icon class="fa-sm" :icon="['fal', 'thumbs-up']" />
        {{ $t("thank you") }}
      </button>
    </template>
  </ConfirmationModal>
</template>

<script>
import { mapState, mapGetters, mapMutations, mapActions } from "vuex";

export default {
  name: "NewCustomer",
  props: {
    type: {
      required: true,
      type: String,
    },
  },
  setup() {
	const api = useAPI();
	return { api };
  },
  data() {
    return {
      userName: "",
      userExist: false,
      email: "",
      emailExist: false,
      password: null,
      enterpassword: false,
      gender: "male",
      firstName: "",
      middleName: "",
      lastName: "",
      selectedRoles: [],

      required: false,
      view: false,
      message: "",
      contexts: {},
      ctx: "",
      activeUserId: null,
      user: {},
      profile: {},
      address: [{}],

      created: false,

      url: "",
    };
  },
  computed: {
    ...mapState({
      active_step: (state) => state.wizard.active_step,
      inputdata: (state) => state.addresses.inputdata,
      roles: (state) => state.rap.roles,
    }),
    ...mapGetters({
      user_exists: "users/user_exists",
      email_exists: "users/email_exists",
    }),
    salutation() {
      if (this.profile.gender === "male") {
        return "Mr.";
      }
      if (this.profile.gender === "female") {
        return "Ms.";
      }
      if (this.profile.gender === "other") {
        return "Mx.";
      }
      return "";
    },
  },
  mounted() {
    if (this.roles && this.roles.length > 0) {
      return;
    } else {
      this.get_roles();
    }

    if (this.type === "customer") {
      this.url = "members";
    } else {
      this.url = "users";
    }
  },
  methods: {
    ...mapActions({
      get_users: "users/get_users",
      get_members: "users/get_members",
      get_roles: "rap/get_roles",
    }),
    ...mapMutations({
      add_user: "users/add_user",
      add_member: "users/add_member",
      set_active_step: "wizard/set_active_step",
      set_modal_name: "users/set_modal_name",
    }),
    validateUserName() {
      if (this.userName) {
        this.userExist = this.user_exists(this.userName);
      } else {
        this.userExist = false;
      }
    },
    validateEmail() {
      if (this.email) {
        this.emailExist = this.email_exists(this.email);
      } else {
        this.emailExist = false;
      }
    },
    updateUserRoles(role) {
      if (this.selectedRoles.includes(role.name)) {
        const index = this.selectedRoles.indexOf(role.name);
        this.selectedRoles.splice(index, 1);
      } else {
        this.selectedRoles.push(role.name);
      }
    },
    async createUser() {
      await this.api
        .post(`/${this.url}`, {
          username: String(Date.now()),
          email: this.email,
          password: this.password,
          gender: this.gender,
          first_name: this.firstName,
          last_name: this.lastName,
          roles: this.selectedRoles,
        })
        .then((response) => {
          this.closeModal();
          // handle until
          this.get_members();
          // this.refresh_users();
          this.activeUserId = response.data.data.id;
          this.api.get(`users/${this.activeUserId}`).then((response) => {
            this.user = response.data;
          });

          this.api
            .get(`users/${this.activeUserId}/profile`)
            .then((response) => {
              this.profile = response.data;
            });
          this.api
            .get(`users/${this.activeUserId}/addresses`)
            .then((response) => {
              this.address = response.data;
            });

          if (this.type === "customer") {
            this.add_member(response.data);
            this.createAddress();
          } else {
            this.add_user(response.data);
            this.created = true;
          }
        })
        .catch((error) => {
          this.required = true;
          this.handleError(error);
        });
    },
    async createAddress() {
      if (this.inputdata.region === null) {
        this.inputdata.region = toString(this.inputdata.region);
      }

      await this.api
        .post(`/members/${this.activeUserId}/addresses`, this.inputdata)
        .then((response) => {
          this.created = true;
          this.api.get(`users/${this.activeUserId}`).then((response) => {
            this.user = response.data;
          });
          this.api
            .get(`members/${this.activeUserId}/profile`)
            .then((response) => {
              this.profile = response.data;
            });
          this.api
            .get(`members/${this.activeUserId}/addresses`)
            .then((response) => {
              this.address = response.data;
            });
        })
        .catch((error) => {
          this.required = true;
          this.handleError(error);
        });
    },
    closeModal() {
      this.$parent.closeModal();
    },
    // refresh_users() {
    // 	if (this.type === "user") {
    // 		this.get_users;
    // 	} else {
    // 		this.get_members;
    // 	}
    // }
  },
};
</script>

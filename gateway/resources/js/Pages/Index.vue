<template>
    <div class="p-4 bg-white rounded-lg shadow-lg">

        <h2 class="text-lg font-bold uppercase tracking-wide text-center mb-4">
            <i class="fal fa-lock-alt mr-2"></i>Login
        </h2>
        <form>
            <div class="my-2">
                <label for="email" class="col-md-4 col-form-label text-md-right">Email</label>

                <div class="col-md-6">
                    <input id="email" type="email" v-model="email"
                           :class="['bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500', ((errors.email) ? 'border border-red-500':'') ]"
                           name="email" required autocomplete="email" autofocus>


                    <span v-if="errors.email" class="text-sm text-red-500" role="alert">
                    <strong> {{ errors.email[0] }} </strong>
                </span>

                </div>
            </div>

            <div class="my-2">
                <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>

                <div class="col-md-6">
                    <input id="password" type="password" v-model="password"
                           :class="['bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500', ((errors.password) ? 'border border-red-500':'') ]"
                           name="password" required autocomplete="current-password">

                    <span v-if="errors.password" class="text-sm text-red-500" role="alert">
                    <strong>{{ errors.password[0] }}</strong>
                </span>

                </div>
            </div>

            <div class="flex items-center my-2">
                <input class="mr-2" type="checkbox" name="remember" id="remember">

                <label class="form-check-label" for="remember">
                    Remember Me
                </label>
            </div>

            <div class="">
                <button @click.prevent="login" type="submit"
                        class="px-4 py-2 block bg-blue-500 rounded w-full text-center text-white transition-colors duration-150 hover:bg-blue-600">
                    <i class="fal fa-key mr-2"></i> Login
                </button>

                <a class="text-gray-500 text-center w-full block mt-4 italic transition-colors hover:text-gray-600"
                   href="">
                    Forgot Your Password?
                </a>

            </div>
        </form>
    </div>

</template>

<script>
import Guest from "../Layouts/Guest";

export default {
    layout: Guest,
    name: "Index",
    data() {
        return {
            email: null,
            password: null,
            errors: {}
        }
    },
    methods: {
        login() {

            axios.post(this.$page.props.loginPage, {
                'email': this.email,
                'password': this.password,
                '_token': this.$page.props.csrf_token
            }).then(res => {
                if (res.status === 204) {
                    window.location.href = '/'
                }
            }).catch(e => {
                console.log(e)
                this.errors = e.response.data.errors
            })

        }
    }
}
</script>

<style scoped>

</style>

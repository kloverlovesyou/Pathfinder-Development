<<<<<<< HEAD
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div
      class="card bg-base-200 border-base-300 rounded-box border p-6 max-w-xs sm:max-w-sm md:max-w-md w-full shadow-lg"
    >
      <h2 class="text-2xl font-semibold text-center mb-6 text-dark-slate">
        Login
      </h2>

      <form @submit.prevent="handleLogin">
        <div class="form-control mb-4">
          <input
            v-model="email"
            class="input validator w-full"
            type="email"
            required
            placeholder="Email"
            name="EmailAddress"
          />
          <p class="validator-hint" v-if="emailError">Invalid Email</p>
        </div>

        <div class="form-control mb-4">
          <input
            v-model="password"
            type="password"
            class="input validator input-bordered w-full"
            required
            placeholder="Password"
            name="Password"
            minlength="8"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must be more than 8 characters, including number, lowercase letter, uppercase letter"
          />
          <p class="validator-hint" v-if="passwordError">Invalid Password</p>
        </div>

        <div class="card-actions justify-center">
          <button
            type="submit"
            class="btn btn-primary w-3/4 bg-dark-slate text-white"
          >
            Log in
          </button>
        </div>
      </form>

      <div class="text-center mt-4">
        <p class="text-sm text-gray-600">
          Don't have an account?
          <router-link to="/typeofaccount" class="text-primary">
            Register here.
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const emailError = ref(false)
const passwordError = ref(false)

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw)

const handleLogin = async () => {
  emailError.value = !validateEmail(email.value)
  passwordError.value = !validatePassword(password.value)

  if (emailError.value || passwordError.value) return

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      emailAddress: email.value,
      password: password.value,
    })

    alert(response.data.message)

    // Optional: Save user or token in storage
    // localStorage.setItem('user', JSON.stringify(response.data.user))

    router.push('/homepage') // Change path as needed
  } catch (error) {
    if (error.response && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Login failed. Please try again.')
    }
  }
}
</script>

<style></style>
=======
<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div
      class="card bg-base-200 border-base-300 rounded-box border p-6 max-w-xs sm:max-w-sm md:max-w-md w-full shadow-lg"
    >
      <h2 class="text-2xl font-semibold text-center mb-6 text-dark-slate">
        Login
      </h2>

      <form @submit.prevent="handleLogin">
        <div class="form-control mb-4">
          <input
            v-model="email"
            class="input validator w-full"
            type="email"
            required
            placeholder="Email"
            name="EmailAddress"
          />
          <p class="validator-hint" v-if="emailError">Invalid Email</p>
        </div>

        <div class="form-control mb-4">
          <input
            v-model="password"
            type="password"
            class="input validator input-bordered w-full"
            required
            placeholder="Password"
            name="Password"
            minlength="8"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must be more than 8 characters, including number, lowercase letter, uppercase letter"
          />
          <p class="validator-hint" v-if="passwordError">Invalid Password</p>
        </div>

        <div class="card-actions justify-center">
          <button
            type="submit"
            class="btn btn-primary w-3/4 bg-dark-slate text-white"
          >
            Log in
          </button>
        </div>
      </form>

      <div class="text-center mt-4">
        <p class="text-sm text-gray-600">
          Don't have an account?
          <router-link to="/typeofaccount" class="text-primary">
            Register here.
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const email = ref('')
const password = ref('')
const emailError = ref(false)
const passwordError = ref(false)

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw)

const handleLogin = async () => {
  emailError.value = !validateEmail(email.value)
  passwordError.value = !validatePassword(password.value)

  if (emailError.value || passwordError.value) return

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      emailAddress: email.value,
      password: password.value,
    })

   

    // Optional: Save user or token in storage
    // localStorage.setItem('user', JSON.stringify(response.data.user))

    router.push('/homepage') // Change path as needed
  } catch (error) {
    if (error.response && error.response.data.message) {
      alert(error.response.data.message)
    } else {
      alert('Login failed. Please try again.')
    }
  }
}
</script>

<style></style>
>>>>>>> 31587a87a46b0131092666d16e648b3ab552aa4b

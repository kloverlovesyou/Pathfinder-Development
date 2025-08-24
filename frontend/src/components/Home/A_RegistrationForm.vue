<template>
  <div class="pb-20 min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div>
      <!-- Header -->
      <div class="flex items-center justify-center">
        <p class="p-5 text-2xl font-semibold font-inter text-dark-slate">
          PathFinder
        </p>
      </div>

      <!-- Back Button -->
      <div class="pt-10 pb-2">
        <button
          class="btn btn-ghost btn-xs sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl text-dark-slate"
          @click="$router.push('/loginform')"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6" />
          </svg>
        </button>
      </div>

      <h2 class="font-inter text-3xl font-semibold text-left mb-6 text-dark-slate pl-3">
        Create Account
      </h2>

      <!-- FORM -->
      <form @submit.prevent="handleSubmit">
        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            required
            placeholder="First Name"
            v-model="form.firstName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            placeholder="Middle Name"
            v-model="form.middleName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            required
            placeholder="Last Name"
            v-model="form.lastName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            required
            placeholder="Address"
            v-model="form.address"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input validator w-full"
            type="email"
            required
            placeholder="Email"
            v-model="form.emailAddress"
          />
          <p class="validator-hint hidden">Invalid Email</p>
        </div>

        <div class="form-control mb-4">
          <input
            type="tel"
            class="input validator tabular-nums w-full"
            required
            placeholder="Phone Number"
            minlength="11"
            maxlength="11"
            pattern="[0-9]*"
            title="Must be 11 digits"
            v-model="form.phoneNumber"
          />
          <p class="hidden validator-hint">Must be 11 digits</p>
        </div>

        <div class="form-control mb-4">
          <input
            type="password"
            class="input validator input-bordered w-full"
            required
            placeholder="Password"
            minlength="8"
            v-model="form.password"
          />
          <p class="validator-hint hidden">
            Must be more than 8 characters, including number, lowercase letter, uppercase letter
          </p>
        </div>

        <div class="form-control mb-4">
          <input
            type="password"
            class="input input-bordered w-full"
            :class="{
              'border-red-500': form.password !== form.confirmPassword,
              'border-green-300': form.password === form.confirmPassword
              
            }"
            required
            placeholder="Confirm Password"
            minlength="8"
            v-model="form.confirmPassword"
          />
          <p
            v-if="form.password !== form.confirmPassword"
            class="text-red-500 text-sm mt-1"
          >
            Passwords do not match.
          </p>
        </div>

        <div>
          <label class="flex items-center space-x-2 cursor-pointer mb-2">
            <input
              type="checkbox"
              class="checkbox validator"
              required
              v-model="termsAccepted"
            />
            <span>Accept all terms and conditions before creating account</span>
          </label>
        </div>

        <div class="card-actions justify-end pt-4">
          <button
            type="submit"
            class="btn btn-primary w-2/4 bg-dark-slate text-white"
          >
            Create
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref({
  firstName: '',
  middleName: '',
  lastName: '',
  address: '',
  emailAddress: '',
  phoneNumber: '',
  password: '',
  confirmPassword: '',
  message: 'password does not match'
})

const termsAccepted = ref(false)

const handleSubmit = async () => {
  if (!termsAccepted.value) {
    alert('You must accept the terms and conditions.')
    return
  }

  if (form.value.password !== form.value.confirmPassword) {
    message.value = 'Passwords do not match.'
    return
  }

  try {
    const response = await axios.post('http://127.0.0.1:8000/api/applicants', {
      ...form.value
    })
    
    router.push('/loginform')
  } 
  
  catch (error) {
    if (error.response && error.response.data.errors) {
      const errors = error.response.data.errors
      alert(Object.values(errors).flat().join('\n'))
    } else {
      alert('Registration failed. Please try again.')
    }
  }
}
</script>

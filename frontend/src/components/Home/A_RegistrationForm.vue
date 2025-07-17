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
        <router-link to="/loginform">
          <button class="btn btn-ghost text-dark-slate">
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
        </router-link>
      </div>

      <!-- Title -->
      <h2 class="font-inter text-3xl font-semibold text-left mb-6 text-dark-slate pl-3">
        Create Account
      </h2>

      <!-- Form -->
      <div class="form-control mb-4">
        <input v-model="form.firstName" class="input w-full" type="text" required placeholder="First Name" />
      </div>

      <div class="form-control mb-4">
        <input v-model="form.middleName" class="input w-full" type="text" required placeholder="Middle Name" />
      </div>

      <div class="form-control mb-4">
        <input v-model="form.lastName" class="input w-full" type="text" required placeholder="Last Name" />
      </div>

      <div class="form-control mb-4">
        <input v-model="form.address" class="input w-full" type="text" required placeholder="Address" />
      </div>

      <div class="form-control mb-4">
        <input
          v-model="form.emailAddress"
          class="input w-full"
          type="email"
          required
          placeholder="Email"
        />
      </div>

      <div class="form-control mb-4">
        <input
          v-model="form.phoneNumber"
          type="tel"
          class="input w-full"
          required
          placeholder="Phone Number"
          pattern="[0-9]*"
          minlength="11"
          maxlength="11"
        />
      </div>

      <div class="form-control mb-4">
        <input
          v-model="form.password"
          type="password"
          class="input input-bordered w-full"
          required
          placeholder="Password"
          minlength="8"
          pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
        />
      </div>

      <div class="form-control mb-4">
        <input
          v-model="form.confirmPassword"
          type="password"
          class="input input-bordered w-full"
          required
          placeholder="Confirm Password"
          minlength="8"
          pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
        />
      </div>

      <div>
        <label class="flex items-center space-x-2 cursor-pointer mb-2">
          <input
            type="checkbox"
            class="checkbox"
            required
            v-model="termsAccepted"
          />
          <span>Accept all terms and conditions before creating account</span>
        </label>
      </div>

      <!-- Submit -->
      <div class="card-actions justify-end pt-4">
        <button
          @click="handleSubmit"
          class="btn btn-primary w-2/4 bg-dark-slate text-white"
        >
          Create
        </button>
      </div>
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
  confirmPassword: ''
})

const termsAccepted = ref(false)

const handleSubmit = async () => {
  if (form.value.password !== form.value.confirmPassword) {
    alert('Passwords do not match.')
    return
  }

  if (!termsAccepted.value) {
    alert('You must accept the terms and conditions.')
    return
  }

  try {
    const payload = { ...form.value }
    delete payload.confirmPassword

    await axios.post('http://127.0.0.1:8000/api/applicants', payload)

    alert('Account created successfully!')
    router.push('/loginform')
  } catch (error) {
    console.error(error)
    alert('Registration failed. Please check your inputs or try again.')
  }
}
</script>
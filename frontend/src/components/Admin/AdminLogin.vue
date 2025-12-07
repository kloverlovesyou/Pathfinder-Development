<template>
  <div class="font-poppins min-h-screen flex items-center justify-center p-4">
    <div class="relative w-full max-w-md">
      <div
        class="card bg-base-200 border-base-300 rounded-box border p-6 shadow-lg transition duration-300"
      >
        <h2 class="text-2xl font-semibold text-center mb-4 text-dark-slate">
          Admin Login
        </h2>

        <form @submit.prevent="handleAdminLogin">
          <!-- Email -->
          <div class="form-control mb-2">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
            </label>
            <input
              v-model="email"
              class="input validator w-full"
              type="email"
              required
              placeholder="Admin Email"
            />
            <p class="validator-hint" v-if="emailError">Invalid Email</p>
          </div>

          <!-- Password -->
          <div class="form-control relative mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></span>
            </label>
            <input
              :type="showPassword ? 'text' : 'password'"
              class="w-full border rounded-lg input pr-10"
              required
              placeholder="Password"
              v-model="password"
            />

            <!-- Toggle Password -->
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center text-gray-500"
              aria-label="Toggle password visibility"
              @click="showPassword = !showPassword"
            >
              <span v-if="showPassword">
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M15.5799 11.9999C15.5799 13.9799 13.9799 15.5799 11.9999 15.5799C10.0199 15.5799 8.41992 13.9799 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C13.9799 8.41992 15.5799 10.0199 15.5799 11.9999Z"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12.0001 20.27C15.5301 20.27 18.8201 18.19 21.1101 14.59C22.0101 13.18 22.0101 10.81 21.1101 9.39997C18.8201 5.79997 15.5301 3.71997 12.0001 3.71997C8.47009 3.71997 5.18009 5.79997 2.89009 9.39997C1.99009 10.81 1.99009 13.18 2.89009 14.59C5.18009 18.19 8.47009 20.27 12.0001 20.27Z"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <span v-else>
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M14.5299 9.46992L9.46992 14.5299C8.81992 13.8799 8.41992 12.9899 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C12.9899 8.41992 13.8799 8.81992 14.5299 9.46992Z"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M17.8201 5.76998C16.0701 4.44998 14.0701 3.72998 12.0001 3.72998C8.47009 3.72998 5.18009 5.80998 2.89009 9.40998C1.99009 10.82 1.99009 13.19 2.89009 14.6C3.68009 15.84 4.60009 16.91 5.60009 17.77"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M8.41992 19.5299C9.55992 20.0099 10.7699 20.2699 11.9999 20.2699C15.5299 20.2699 18.8199 18.1899 21.1099 14.5899C22.0099 13.1799 22.0099 10.8099 21.1099 9.39993C20.7799 8.87993 20.4199 8.38993 20.0499 7.92993"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M15.5099 12.7C15.2499 14.11 14.0999 15.26 12.6899 15.52"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M9.47 14.53L2 22"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M22 2L14.53 9.47"
                    stroke="#292D32"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
            </button>
          </div>

          <div class="card-actions justify-center">
          <button
            type="submit"
            class="btn btn-primary w-3/4 bg-dark-slate text-white flex items-center justify-center gap-2 disabled:opacity-60"
            :disabled="isLoading"
          >
            <!-- 🔥 IF LOADING -->
            <span v-if="isLoading" class="flex items-center gap-2">
              <svg
                class="animate-spin h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
                ></path>
              </svg>
              Logging in...
            </span>

            <!-- 🔥 IF NOT LOADING -->
            <span v-else>
              Log in
            </span>
          </button>
          </div>
        </form>

        <!-- Error -->
        <p v-if="loginError" class="text-center mt-4 text-red-600 font-medium">
          {{ loginError }}
        </p>

        <!-- Toast (bottom-right) -->
        <div class="toast toast-end toast-top z-50" v-if="toastMessage">
          <div class="alert alert-error text-white">
            <span>{{ toastMessage }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { authState, login } from "@/stores/authState";
const router = useRouter();

const email = ref("");
const password = ref("");
const showPassword = ref(false);
const emailError = ref(false);
const loginError = ref("");
const toastMessage = ref("");
const isLoading = ref(false);


// Email validator
const validateEmail = (emailVal) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);

const showToast = (msg) => {
  toastMessage.value = msg;
  setTimeout(() => (toastMessage.value = ""), 3000);
};


const handleAdminLogin = async () => {
  loginError.value = "";
  emailError.value = !validateEmail(email.value);
  if (emailError.value) return;

  isLoading.value = true; // 🔥 Start loader

  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/admin/login",
      {
        emailAddress: email.value,
        password: password.value,
      }
    );

    const adminData = res.data.admin;
    const token = res.data.token;

    // Save admin login in localStorage
    localStorage.setItem("admin_token", token);
    localStorage.setItem(
      "user",
      JSON.stringify({ ...adminData, role: "admin" })
    );

    // Update reactive store
    login({ ...adminData, role: "admin" }, token);

    // Clear fields
    email.value = "";
    password.value = "";

    // Redirect
    router.push("/admin/dashboard");
  } catch (err) {
    console.error("Admin login error:", err);

    if (err.response?.status === 401) {
      showToast("Incorrect email or password.");
    } else {
      showToast("Login failed. Try again.");
    }
  } finally {
    isLoading.value = false; // 🔥 Stop loader ALWAYS
  }
};

</script>

<style>
input::-ms-reveal,
input::-ms-clear {
  display: none;
}
input::-webkit-credentials-auto-fill-button {
  visibility: hidden;
}
</style>
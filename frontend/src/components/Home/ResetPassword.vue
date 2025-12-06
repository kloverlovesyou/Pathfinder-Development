<template>
  <div class="font-poppins min-h-screen flex items-center justify-center p-4">
    <div class="relative w-full max-w-md">
      <div
        class="card bg-base-200 border-base-300 rounded-box border p-6 shadow-lg transition duration-300"
      >
        <h2 class="text-2xl font-semibold text-center mb-4 text-dark-slate">
          Reset Password
        </h2>

        <div v-if="!token || !type" class="text-center">
          <p class="text-red-600 mb-4">Invalid reset link. Please request a new password reset.</p>
          <router-link to="/auth/login" class="btn btn-primary bg-dark-slate text-white">
            Go to Login
          </router-link>
        </div>

        <form v-else @submit.prevent="handleResetPassword">
          <!-- New Password -->
          <div class="form-control relative mb-2">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">New Password <span class="text-red-500">*</span></span>
            </label>
            <input
              :type="showPassword ? 'text' : 'password'"
              class="w-full border rounded-lg input"
              required
              placeholder="New Password"
              v-model="password"
              pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
              title="Must contain at least 8 characters, including a number, a lowercase and an uppercase letter"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
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
            <p class="validator-hint" v-if="passwordError">
              Password must contain at least 8 characters, including a number, a lowercase and an uppercase letter
            </p>
          </div>

          <!-- Confirm Password -->
          <div class="form-control relative mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Confirm New Password <span class="text-red-500">*</span></span>
            </label>
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              class="w-full border rounded-lg input"
              required
              placeholder="Confirm New Password"
              v-model="confirmPassword"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500"
              aria-label="Toggle confirm password visibility"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <span v-if="showConfirmPassword">
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
            <p class="validator-hint" v-if="confirmPasswordError">
              Passwords do not match
            </p>
          </div>

          <div class="card-actions justify-center">
            <button
              type="submit"
              class="btn btn-primary w-3/4 bg-dark-slate text-white flex items-center justify-center gap-2 disabled:opacity-60"
              :disabled="isLoading"
            >
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
                Resetting...
              </span>
              <span v-else> Reset Password </span>
            </button>
          </div>
        </form>

        <p v-if="errorMessage" class="text-center mt-4 text-red-600 font-medium">
          {{ errorMessage }}
        </p>

        <p v-if="successMessage" class="text-center mt-4 text-green-600 font-medium">
          {{ successMessage }}
        </p>

        <div class="text-center mt-4">
          <router-link to="/auth/login" class="text-sm text-primary hover:underline">
            Back to Login
          </router-link>
        </div>

        <!-- Toast -->
        <div class="toast toast-end toast-top z-50" v-if="toastMessage">
          <div class="alert" :class="toastType === 'error' ? 'alert-error' : 'alert-success'">
            <span>{{ toastMessage }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";

const router = useRouter();
const route = useRoute();

const token = ref("");
const type = ref("");
const password = ref("");
const confirmPassword = ref("");
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordError = ref(false);
const confirmPasswordError = ref(false);
const isLoading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const toastMessage = ref("");
const toastType = ref("");

const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw);

const showToast = (msg, type = "success") => {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = "";
  }, 3000);
};

onMounted(() => {
  token.value = route.query.token || "";
  type.value = route.query.type || "";
});

const handleResetPassword = async () => {
  passwordError.value = !validatePassword(password.value);
  confirmPasswordError.value = password.value !== confirmPassword.value;
  errorMessage.value = "";
  successMessage.value = "";

  if (passwordError.value || confirmPasswordError.value) {
    if (passwordError.value) {
      errorMessage.value =
        "Password must contain at least 8 characters, including a number, a lowercase and an uppercase letter";
    } else if (confirmPasswordError.value) {
      errorMessage.value = "Passwords do not match";
    }
    return;
  }

  isLoading.value = true;

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/reset-password",
      {
        token: token.value,
        type: type.value,
        password: password.value,
      }
    );

    if (response.data.status === "success") {
      successMessage.value = response.data.message;
      showToast(response.data.message, "success");
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        router.push("/auth/login");
      }, 3000);
    }
  } catch (err) {
    console.error("Reset password error:", err);
    errorMessage.value =
      err.response?.data?.message || "Failed to reset password. Please try again.";
    showToast(errorMessage.value, "error");
  } finally {
    isLoading.value = false;
  }
};
</script>

<style>
/* Hide Chrome/Edge/Safari built-in "eye" icon */
input::-ms-reveal,
input::-ms-clear {
  display: none;
}

input::-webkit-credentials-auto-fill-button {
  visibility: hidden;
}
</style>


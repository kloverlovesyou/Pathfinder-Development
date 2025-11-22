<template>
  <div class="font-poppins min-h-screen flex items-center justify-center p-4">
    <!-- Wrapper -->
    <div class="relative w-full max-w-md">
      <!-- Login Card -->
      <div
        class="card bg-base-200 border-base-300 rounded-box border p-6 shadow-lg transition duration-300"
      >
        <h2 class="text-2xl font-semibold text-center mb-4 text-dark-slate">
          Login
        </h2>

        <form @submit.prevent="handleLogin">
          <!-- Email -->
          <div class="form-control mb-2">
            <input
              v-model="email"
              class="input validator w-full"
              type="email"
              required
              placeholder="Email"
            />
            <p class="validator-hint" v-if="emailError">Invalid Email</p>
          </div>

          <!-- Password -->
          <div class="form-control relative mb-4">
            <input
              :type="showPassword ? 'text' : 'password'"
              class="w-full border rounded-lg input"
              required
              placeholder="Password"
              v-model="password"
              pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
              title="Must contain at least 8 characters, including a number, a lowercase and an uppercase letter"
            />

            <!-- 👁 Toggle Button -->
            <button
              type="button"
              class="absolute right-3 top-3 text-gray-500"
              aria-label="Toggle confirm password visibility"
              @click="showPassword = !showPassword"
            >
              <span v-if="showPassword"
                ><svg
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
              <span v-else
                ><svg
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
              class="btn btn-primary w-3/4 bg-dark-slate text-white"
            >
              Log in
            </button>
          </div>
        </form>

        <!-- 🔴 Error message below Login -->
        <p v-if="loginError" class="text-center mt-4 text-red-600 font-medium">
          {{ loginError }}
        </p>

        <div class="text-center mt-4">
          <p class="text-sm text-gray-600">
            Don't have an account?
            <router-link to="/typeofaccount" class="text-primary">
              Register here.
            </router-link>
          </p>
        </div>
                <!-- Toast (bottom-right) -->
        <div class="toast toast-end toast-top z-50" v-if="toastMessage">
          <div class="alert alert-error text-white">
            <span>{{ toastMessage }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Email Verification Modal -->
    <div v-if="showVerificationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="closeVerificationModal">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-gray-800">Verify Your Email</h3>
          <button @click="closeVerificationModal" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <div class="flex items-center justify-center mb-4">
            <svg class="w-16 h-16 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <p class="text-gray-700 mb-2">
            Please verify your email address before logging in.
          </p>
          <p class="text-sm text-gray-600 mb-4">
            We've sent a verification link to <strong>{{ email }}</strong>. Please check your inbox and click the verification link.
          </p>
          <p class="text-sm text-gray-500 mb-4">
            Didn't receive the email? Check your spam folder or click the button below to resend.
          </p>
        </div>

        <div class="flex flex-col gap-2">
          <button
            @click="resendVerificationEmail"
            :disabled="resendingEmail"
            class="btn btn-primary w-full bg-dark-slate text-white disabled:opacity-50"
          >
            <span v-if="resendingEmail">Sending...</span>
            <span v-else>Resend Verification Email</span>
          </button>
          <button
            @click="closeVerificationModal"
            class="btn btn-outline w-full"
          >
            Close
          </button>
        </div>

        <div v-if="resendMessage" class="mt-4 p-3 rounded" :class="resendMessageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
          <p class="text-sm">{{ resendMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useRegistrationStore } from "@/stores/registrationStore"; 
const regStore = useRegistrationStore();

const router = useRouter();
const showPassword = ref(false);
const email = ref("");
const password = ref("");
const emailError = ref(false);
const passwordError = ref(false);
const toastMessage = ref("");

const loginError = ref(""); // just show as text now
const showVerificationModal = ref(false);
const resendingEmail = ref(false);
const resendMessage = ref("");
const resendMessageType = ref(""); // 'success' or 'error'
const userType = ref(""); // 'applicant' or 'organization'

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw);

  const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
      toastMessage.value = "";
    }, 3000); // Toast disappears after 3s
  };

const handleLogin = async () => {
  emailError.value = !validateEmail(email.value);
  passwordError.value = !validatePassword(password.value);
  loginError.value = "";

  if (emailError.value || passwordError.value) return;

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/login",
      {
        emailAddress: email.value,
        password: password.value,
      }
    );

    const userData = response.data.user || response.data.organization;
    const token = response.data.token;
    const role = userData.role || (userData.adminID ? "organization" : "applicant");

    // Handle pending status
    if (role === "organization" && userData.status === "pending") {
      showToast("Your organization account is not yet approved by the admin.");
      return;
    }

    // Handle rejected status with reason
    if (role === "organization" && userData.status === "rejected") {
      const reason = response.data.reason; // backend returns rejection reason
      showToast(`Your registration was rejected. Reason: ${reason}`);
      return;
    }

    let displayName = "";
    if (role === "organization") {
      displayName = userData.organizationName || userData.name || "Organization";
    } else if (role === "admin") {
      displayName = userData.name || "Admin";
    } else {
      displayName = `${userData.firstName} ${userData.lastName}`;
    }

    // Save token + user
    localStorage.setItem("token", token);
    localStorage.setItem(
      "user",
      JSON.stringify({ ...userData, role, displayName })
    );

    await regStore.fetchMyRegistrations();

    // Redirect
    if (role === "organization") {
      router.push("/organization");
    } else if (role === "admin") {
      router.push("/admin");
    } else {
      router.push("/app");
    }

      } catch (err) {
  console.error("FULL ERROR RESPONSE:", err);

  if (err.response?.status === 403) {
    let data = err.response.data;
    console.log("FULL RESPONSE DATA:", data);

    // Ensure reason exists
    const reason = data && typeof data === "object" ? data.reason : undefined;
    console.log("REJECTION REASON:", reason);

    const msg = reason
      ? `Your registration was rejected. Reason: ${reason}`
      : data.message || "Your registration is not approved.";

    showToast(msg);
    return;
  }

  showToast("Invalid credentials. Please try again.");
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

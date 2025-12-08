<template>
  <div class="font-poppins w-full flex items-center justify-center min-h-screen md:min-h-[600px] py-4 md:py-8">
    <!-- Main Card Container -->
    <div class="relative w-full max-w-4xl">
      <div class="bg-white rounded-2xl shadow-2xl relative animated-border">
        <div class="overflow-hidden rounded-2xl">
        <!-- Desktop: Horizontal Layout (side-by-side) -->
        <div class="hidden md:flex relative w-full h-[600px] md:h-[650px] overflow-hidden">
          <!-- Left Side: Welcome Panel with Typing Animation -->
          <div
            class="absolute inset-y-0 left-0 w-2/5 bg-gradient-to-br from-customButton to-dark-slate z-40 flex items-center justify-center p-8"
          >
            <div class="text-center text-white">
              <h2 class="text-3xl md:text-4xl font-bold mb-4 min-h-[3rem]">
                <span class="typing-text">{{ displayedText }}</span><span class="typing-cursor">|</span>
              </h2>
            </div>
          </div>

          <!-- Login Form Container (Right Side) -->
          <div
            class="absolute inset-y-0 right-0 w-3/5 bg-white z-30 flex items-center"
            style="pointer-events: auto;"
          >
            <div class="p-8 h-full w-full flex flex-col justify-center">
              <!-- Login Form -->
              <div class="w-full">
                <h2 class="text-3xl font-semibold mb-6 text-dark-slate">Login</h2>
                
                <form @submit.prevent="otpRequired ? handleOTPVerification() : handleAdminLogin()" class="space-y-4">
                  <!-- Email -->
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="email"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton"
                      type="email"
                      required
                      placeholder="Admin Email"
                    />
                    <p class="validator-hint text-red-500 text-sm mt-1" v-if="emailError">Invalid Email</p>
                  </div>

                  <!-- Password -->
                  <div class="form-control relative">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></span>
                    </label>
                    <div class="relative">
                      <input
                        :type="showPassword ? 'text' : 'password'"
                        class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton pr-10"
                        required
                        placeholder="Password"
                        v-model="password"
                      />
                      <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-500 hover:text-dark-slate h-5 w-5"
                        aria-label="Toggle password visibility"
                        @click="showPassword = !showPassword"
                      >
                        <svg
                          v-if="showPassword"
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M15.5799 11.9999C15.5799 13.9799 13.9799 15.5799 11.9999 15.5799C10.0199 15.5799 8.41992 13.9799 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C13.9799 8.41992 15.5799 10.0199 15.5799 11.9999Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M12.0001 20.27C15.5301 20.27 18.8201 18.19 21.1101 14.59C22.0101 13.18 22.0101 10.81 21.1101 9.39997C18.8201 5.79997 15.5301 3.71997 12.0001 3.71997C8.47009 3.71997 5.18009 5.79997 2.89009 9.39997C1.99009 10.81 1.99009 13.18 2.89009 14.59C5.18009 18.19 8.47009 20.27 12.0001 20.27Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <svg
                          v-else
                          width="24"
                          height="24"
                          viewBox="0 0 24 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M14.5299 9.46992L9.46992 14.5299C8.81992 13.8799 8.41992 12.9899 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C12.9899 8.41992 13.8799 8.81992 14.5299 9.46992Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M17.8201 5.76998C16.0701 4.44998 14.0701 3.72998 12.0001 3.72998C8.47009 3.72998 5.18009 5.80998 2.89009 9.40998C1.99009 10.82 1.99009 13.19 2.89009 14.6C3.68009 15.84 4.60009 16.91 5.60009 17.77"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M8.41992 19.5299C9.55992 20.0099 10.7699 20.2699 11.9999 20.2699C15.5299 20.2699 18.8199 18.1899 21.1099 14.5899C22.0099 13.1799 22.0099 10.8099 21.1099 9.39993C20.7799 8.87993 20.4199 8.38993 20.0499 7.92993"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M15.5099 12.7C15.2499 14.11 14.0999 15.26 12.6899 15.52"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M9.47 14.53L2 22"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M22 2L14.53 9.47"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- OTP Input (shown when OTP is required) -->
                  <div v-if="otpRequired" class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Verification Code <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="otp"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton text-center text-2xl tracking-widest"
                      type="text"
                      maxlength="6"
                      required
                      placeholder="000000"
                      @input="formatOTP"
                    />
                    <p class="text-sm text-gray-600 mt-2 text-center">
                      Enter the 6-digit code sent to your email
                    </p>
                    <p v-if="otpError" class="validator-hint text-red-500 text-sm mt-1 text-center">{{ otpError }}</p>
                  </div>

                  <!-- Error Message -->
                  <p v-if="loginError" class="text-center text-red-600 font-medium text-sm">
                    {{ loginError }}
                  </p>

                  <!-- Submit Button -->
                  <button
                    type="submit"
                    class="btn w-full bg-dark-slate text-white border-none hover:bg-slate-800 transition-colors disabled:opacity-60 rounded-lg font-semibold"
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
                      Logging in...
                    </span>
                    <span v-else>
                      {{ otpRequired ? 'Verify Code' : 'LOG IN' }}
                    </span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: Vertical Layout -->
        <div class="block md:hidden relative w-full min-h-screen overflow-hidden">
          <!-- Welcome Panel (Top) -->
          <div
            class="absolute inset-x-0 top-0 h-1/3 bg-gradient-to-br from-customButton to-dark-slate z-40 flex items-center justify-center p-4 sm:p-6"
          >
            <div class="text-center text-white">
              <h2 class="text-xl sm:text-2xl font-bold mb-2 sm:mb-3 min-h-[2rem]">
                <span class="typing-text">{{ displayedText }}</span><span class="typing-cursor">|</span>
              </h2>
            </div>
          </div>

          <!-- Login Form Container (Bottom) -->
          <div
            class="absolute inset-x-0 bottom-0 h-2/3 bg-white z-30 flex items-center overflow-y-auto"
          >
            <div class="p-4 sm:p-6 h-full w-full flex flex-col justify-center min-h-0">
              <!-- Login Form -->
              <div class="w-full">
                <h2 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-dark-slate">Login</h2>
                
                <form @submit.prevent="otpRequired ? handleOTPVerification() : handleAdminLogin()" class="space-y-2 sm:space-y-3">
                  <!-- Email -->
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="email"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton text-sm"
                      type="email"
                      required
                      placeholder="Admin Email"
                    />
                    <p class="validator-hint text-red-500 text-xs mt-1" v-if="emailError">Invalid Email</p>
                  </div>

                  <!-- Password -->
                  <div class="form-control relative">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></span>
                    </label>
                    <div class="relative">
                      <input
                        :type="showPassword ? 'text' : 'password'"
                        class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton pr-10 text-sm"
                        required
                        placeholder="Password"
                        v-model="password"
                      />
                      <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-500 hover:text-dark-slate h-5 w-5"
                        aria-label="Toggle password visibility"
                        @click="showPassword = !showPassword"
                      >
                        <svg
                          v-if="showPassword"
                          width="20"
                          height="20"
                          viewBox="0 0 24 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M15.5799 11.9999C15.5799 13.9799 13.9799 15.5799 11.9999 15.5799C10.0199 15.5799 8.41992 13.9799 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C13.9799 8.41992 15.5799 10.0199 15.5799 11.9999Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M12.0001 20.27C15.5301 20.27 18.8201 18.19 21.1101 14.59C22.0101 13.18 22.0101 10.81 21.1101 9.39997C18.8201 5.79997 15.5301 3.71997 12.0001 3.71997C8.47009 3.71997 5.18009 5.79997 2.89009 9.39997C1.99009 10.81 1.99009 13.18 2.89009 14.59C5.18009 18.19 8.47009 20.27 12.0001 20.27Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <svg
                          v-else
                          width="20"
                          height="20"
                          viewBox="0 0 24 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M14.5299 9.46992L9.46992 14.5299C8.81992 13.8799 8.41992 12.9899 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C12.9899 8.41992 13.8799 8.81992 14.5299 9.46992Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M17.8201 5.76998C16.0701 4.44998 14.0701 3.72998 12.0001 3.72998C8.47009 3.72998 5.18009 5.80998 2.89009 9.40998C1.99009 10.82 1.99009 13.19 2.89009 14.6C3.68009 15.84 4.60009 16.91 5.60009 17.77"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M8.41992 19.5299C9.55992 20.0099 10.7699 20.2699 11.9999 20.2699C15.5299 20.2699 18.8199 18.1899 21.1099 14.5899C22.0099 13.1799 22.0099 10.8099 21.1099 9.39993C20.7799 8.87993 20.4199 8.38993 20.0499 7.92993"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M15.5099 12.7C15.2499 14.11 14.0999 15.26 12.6899 15.52"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M9.47 14.53L2 22"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M22 2L14.53 9.47"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- OTP Input (shown when OTP is required) -->
                  <div v-if="otpRequired" class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Verification Code <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="otp"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton text-center text-xl sm:text-2xl tracking-widest text-sm"
                      type="text"
                      maxlength="6"
                      required
                      placeholder="000000"
                      @input="formatOTP"
                    />
                    <p class="text-xs sm:text-sm text-gray-600 mt-2 text-center">
                      Enter the 6-digit code sent to your email
                    </p>
                    <p v-if="otpError" class="validator-hint text-red-500 text-xs mt-1 text-center">{{ otpError }}</p>
                  </div>

                  <!-- Error Message -->
                  <p v-if="loginError" class="text-center text-red-600 font-medium text-xs">
                    {{ loginError }}
                  </p>

                  <!-- Submit Button -->
                  <button
                    type="submit"
                    class="btn w-full bg-dark-slate text-white border-none hover:bg-slate-800 transition-colors disabled:opacity-60 rounded-lg font-semibold text-sm py-2"
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
                      Logging in...
                    </span>
                    <span v-else>
                      {{ otpRequired ? 'Verify Code' : 'LOG IN' }}
                    </span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>

    <!-- Toast (bottom-right) -->
    <div class="toast toast-end toast-top z-50" v-if="toastMessage">
      <div class="alert alert-error text-white">
        <span>{{ toastMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { authState, login } from "@/stores/authState";
const router = useRouter();

const email = ref("");
const password = ref("");
const otp = ref("");
const showPassword = ref(false);
const emailError = ref(false);
const loginError = ref("");
const otpError = ref("");
const toastMessage = ref("");
const isLoading = ref(false);
const otpRequired = ref(false);

// Typing animation
const fullText = "Hello Admin!";
const displayedText = ref("");
let typingTimeout = null;
let isDeleting = false;

const typeText = () => {
  let currentIndex = 0;
  displayedText.value = "";
  isDeleting = false;
  
  const type = () => {
    if (!isDeleting) {
      // Typing phase
      if (currentIndex < fullText.length) {
        displayedText.value += fullText[currentIndex];
        currentIndex++;
        typingTimeout = setTimeout(type, 100); // Typing speed: 100ms per character
      } else {
        // Finished typing, wait then start deleting
        isDeleting = true;
        typingTimeout = setTimeout(type, 2000); // Wait 2 seconds before deleting
      }
    } else {
      // Deleting phase
      if (displayedText.value.length > 0) {
        displayedText.value = displayedText.value.slice(0, -1);
        typingTimeout = setTimeout(type, 50); // Deleting speed: 50ms per character
      } else {
        // Finished deleting, restart typing
        isDeleting = false;
        currentIndex = 0;
        typingTimeout = setTimeout(type, 500); // Brief pause before restarting
      }
    }
  };
  
  type();
};

onMounted(() => {
  typeText();
});

onUnmounted(() => {
  if (typingTimeout) {
    clearTimeout(typingTimeout);
  }
});


// Email validator
const validateEmail = (emailVal) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);

const showToast = (msg) => {
  toastMessage.value = msg;
  setTimeout(() => (toastMessage.value = ""), 3000);
};


// Format OTP input to only allow numbers
const formatOTP = (event) => {
  const value = event.target.value.replace(/\D/g, ''); // Remove non-digits
  otp.value = value.slice(0, 6); // Limit to 6 digits
};

const handleAdminLogin = async () => {
  loginError.value = "";
  otpError.value = "";
  emailError.value = !validateEmail(email.value);
  if (emailError.value) return;

  isLoading.value = true;

  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/admin/login",
      {
        emailAddress: email.value,
        password: password.value,
      }
    );

    // Check if OTP is required
    if (res.data.otp_required) {
      otpRequired.value = true;
      showToast("OTP sent to your email. Please check your inbox.");
    } else {
      // Legacy flow (shouldn't happen with new implementation)
      const adminData = res.data.admin;
      const token = res.data.token;

      localStorage.setItem("admin_token", token);
      localStorage.setItem(
        "user",
        JSON.stringify({ ...adminData, role: "admin" })
      );

      login({ ...adminData, role: "admin" }, token);
      router.push("/admin/dashboard");
    }
  } catch (err) {
    console.error("Admin login error:", err);

    if (err.response?.status === 401) {
      showToast("Incorrect email or password.");
    } else {
      showToast(err.response?.data?.message || "Login failed. Try again.");
    }
  } finally {
    isLoading.value = false;
  }
};

const handleOTPVerification = async () => {
  otpError.value = "";
  
  if (otp.value.length !== 6) {
    otpError.value = "Please enter a valid 6-digit code.";
    return;
  }

  isLoading.value = true;

  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/admin/verify-otp",
      {
        emailAddress: email.value,
        password: password.value,
        otp: otp.value,
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
    otp.value = "";
    otpRequired.value = false;

    // Redirect
    router.push("/admin/dashboard");
  } catch (err) {
    console.error("OTP verification error:", err);

    if (err.response?.status === 401) {
      otpError.value = err.response?.data?.message || "Invalid OTP. Please try again.";
      showToast(err.response?.data?.message || "Invalid OTP. Please try again.");
    } else {
      showToast(err.response?.data?.message || "Verification failed. Try again.");
    }
  } finally {
    isLoading.value = false;
  }
};

</script>

<style scoped>
input::-ms-reveal,
input::-ms-clear {
  display: none;
}
input::-webkit-credentials-auto-fill-button {
  visibility: hidden;
}

/* Typing animation cursor */
.typing-cursor {
  display: inline-block;
  animation: blink 1s infinite;
  margin-left: 2px;
}

@keyframes blink {
  0%, 50% {
    opacity: 1;
  }
  51%, 100% {
    opacity: 0;
  }
}

/* Animated running light border effect */
.animated-border {
  position: relative;
  padding: 4px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6);
  background-size: 400% 100%;
  border-radius: 1rem;
  animation: borderFlow 4s ease infinite;
}

.animated-border > div {
  background: white;
  border-radius: calc(1rem - 4px);
  width: 100%;
  position: relative;
  z-index: 1;
}

@keyframes borderFlow {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}
</style>
<template>
  <div
    class="font-poppins pb-20 min-h-screen flex items-center justify-center p-4"
  >
    <div>
      <div>
        <div class="flex items-center justify-center">
          <p class="p-5 text-2xl font-semibold text-dark-slate">Pathfinder</p>
        </div>
        <div class="pt-10 pb-2">
          <button
            class="btn btn-ghost btn-xs sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl text-dark-slate"
            @click="$router.push('Login')"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 18l-6-6 6-6"
              />
            </svg>
          </button>
        </div>

        <h2 class="text-3xl font-semibold text-left mb-6 text-dark-slate pl-3">
          Create Account
        </h2>
      </div>
      <form @submit.prevent="handleSubmit">
        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            required
            placeholder="Organization Name"
            name="name"
            v-model="form.name"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="text"
            required
            placeholder="Location"
            name="location"
            v-model="form.location"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full"
            type="url"
            placeholder="Website URL"
            name="websiteURL"
            v-model="form.websiteURL"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input validator w-full"
            type="email"
            required
            placeholder="Email"
            name="emailAddress"
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

        <div class="form-control mb-4 relative">
          <input
            :type="showPassword ? 'text' : 'password'"
            class="input validator w-full pr-10"
            required
            placeholder="Password"
            v-model="form.password"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
            title="Must contain at least 8 characters, including a number, a lowercase and an uppercase letter"
          />

          <!-- 👁 Toggle Button -->
          <button
            type="button"
            class="absolute right-2 top-1/3 -translate-y-1/2 text-gray-500"
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

          <p class="validator-hint text-gray-500 text-sm mt-1">
            <i>
              *Must be at least 8 characters, and include a number, a lowercase
              letter, and an uppercase letter.
            </i>
          </p>
        </div>

        <!-- Confirm Password -->
        <div class="form-control mb-4 relative">
          <input
            :type="showConfirm ? 'text' : 'password'"
            class="input w-full pr-10"
            :class="{
              'border-green-500':
                form.confirmPassword && form.password === form.confirmPassword,
              'border-red-500':
                form.confirmPassword && form.password !== form.confirmPassword,
            }"
            required
            placeholder="Confirm Password"
            minlength="8"
            v-model="form.confirmPassword"
          />

          <!-- Toggle Button -->
          <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500"
            @click="showConfirm = !showConfirm"
            aria-label="Toggle confirm password visibility"
          >
            <span v-if="showConfirm"
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

          <!-- Validation message -->
          <p
            v-if="
              form.confirmPassword && form.password !== form.confirmPassword
            "
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
            <span>
              <a
                href="#"
                class="text-blue-500 underline"
                @click.prevent="showModal = true"
              >
                Accept all terms and conditions.
              </a>
            </span>
          </label>

          <!-- Modal -->
          <div
            v-if="showModal"
            class="fixed inset-0 flex items-center justify-center z-50"
            style="background-color: rgba(255, 255, 255, 0.3)"
          >
            <div class="bg-white p-6 rounded-lg w-96 relative">
              <h2 class="text-lg font-bold mb-4">Terms and Conditions</h2>
              <p class="text-sm mb-4">
                <!-- Place your terms content here -->
                By using this system and submitting your personal information,
                you acknowledge and agree that:
              </p>
              <p class="text-sm mb-4">
                Consent to Data Processing: You voluntarily consent to the
                collection, processing, and storage of your personal data by the
                system.<br />
                Compliance with the Data Privacy Act: Your personal data will be
                handled in accordance with the Data Privacy Act of 2012
                (Republic Act No. 10173), ensuring its confidentiality,
                integrity, and security. <br />Purpose Limitation: Your personal
                data will be used solely for legitimate purposes related to the
                services provided by this system. <br />
                Subject Rights: You have the right to access, correct, or
                request deletion of your personal data as provided under the
                law. <br /><br />
                By checking the box and proceeding, you indicate your
                understanding and acceptance of these terms.
              </p>
              <button
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-800"
                @click="showModal = false"
              >
                ✕
              </button>
            </div>
          </div>
        </div>

        <div class="card-actions justify-end pt-4">
          <button class="btn btn-primary w-2/4 bg-dark-slate text-white">
            Create
          </button>
        </div>
      </form>
    </div>

        <!-- 🔵 Success Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 flex items-center justify-center z-50"
      style="background-color: rgba(0, 0, 0, 0.3)"
    >
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full text-center relative">
        <h2 class="text-lg font-bold text-green-600 mb-4">
          Registration Successful 🎉
        </h2>
        <p class="text-sm mb-6">
          Your organization account has been created successfully.<br />
          Click OK to go to the login page.
        </p>
        <button
          class="btn btn-primary w-3/4 bg-dark-slate text-white"
          @click="goToLogin"
        >
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const form = ref({
  name: "",
  location: "",
  websiteURL: "",
  emailAddress: "",
  phoneNumber: "",
  password: "",
  confirmPassword: "",
});

const termsAccepted = ref(false);
const showModal = ref(false);
const showSuccessModal = ref(false);

const handleSubmit = async () => {
  if (!termsAccepted.value) {
    return;
  }
  if (form.value.password !== form.value.confirmPassword) {
    return;
  }

  try {
    const { confirmPassword, ...payload } = form.value;
    await axios.post("http://127.0.0.1:8000/api/organization", payload);

    // ✅ Show success modal instead of alert
    showSuccessModal.value = true;

    // ✅ Clear form
    form.value = {
      name: "",
      location: "",
      websiteURL: "",
      emailAddress: "",
      phoneNumber: "",
      password: "",
      confirmPassword: "",
    };
    termsAccepted.value = false;
  } catch (error) {
    console.error(error.response?.data || error.message);
  }
};

const goToLogin = () => {
  showSuccessModal.value = false;
  router.push("Login");
};

const showPassword = ref(false);
const showConfirm = ref(false);
</script>

<style scoped>
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-textfield-decoration-container {
  display: none !important;
}
</style>

<template>
  <!-- Full height scrollable wrapper -->
  <div class="min-h-screen w-full bg-white overflow-y-auto flex justify-center">
    <!-- Center form horizontally but not vertically -->
    <div class="font-poppins w-full md:w-3/4 lg:w-2/3 xl:w-1/2 p-6">
      <!-- Back Button -->
      <div class="pt-4 pb-2">
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

      <!-- FORM -->
      <form @submit.prevent="handleSubmit">
        <div class="form-control mb-4">
          <input
            class="input w-full bg-gray-100"
            type="text"
            required
            placeholder="First Name"
            v-model="form.firstName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full bg-gray-100"
            type="text"
            placeholder="Middle Name"
            v-model="form.middleName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full bg-gray-100"
            type="text"
            required
            placeholder="Last Name"
            v-model="form.lastName"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input w-full bg-gray-100"
            type="text"
            required
            placeholder="Address"
            v-model="form.address"
          />
        </div>

        <div class="form-control mb-4">
          <input
            class="input validator w-full bg-gray-100"
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
            class="input validator tabular-nums w-full bg-gray-100"
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
            class="input validator w-full pr-10 bg-gray-100"
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
        <div class="form-control mb-4">
          <!-- Input + toggle wrapper -->
          <div class="relative">
            <input
              :type="showConfirm ? 'text' : 'password'"
              class="input w-full pr-10 focus:outline-none focus:border-transparent bg-gray-100"
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
              <!-- Your SVGs -->
              <span v-if="showConfirm">
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

          <!-- Validation message (outside the relative wrapper) -->
          <p
            v-if="
              form.confirmPassword && form.password !== form.confirmPassword
            "
            class="text-red-500 text-sm mt-1"
          >
            Passwords do not match.
          </p>
        </div>

        <div class="divider"></div>

        <div class="relative mb-4">
          <label class="block font-semibold text-gray-500"
            >To help us personalize recommendations for you,
          </label>
          <p class="text-gray-500 mb-2">fill up the fields below.</p>

          <!-- Input box -->
          <div @click="focusInput">
            <input
              ref="inputEl"
              v-model="search"
              type="text"
              @focus="showDropdown = true"
              @blur="hideDropdown"
              @keydown.enter.prevent="addCustomJob"
              placeholder="Type or select desired positions"
              class="input w-full focus:outline-none focus:border-transparent bg-gray-100 text-gray-800 rounded-lg px-3 py-2"
            />
          </div>

          <!-- Dropdown list -->
          <ul
            v-if="showDropdown"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-auto"
          >
            <li
              v-for="(job, index) in filteredJobs.length
                ? filteredJobs
                : jobOptions"
              :key="index"
              @mousedown.prevent="selectJob(job)"
              class="px-4 py-2 hover:bg-blue-100 cursor-pointer"
            >
              {{ job }}
            </li>
          </ul>

          <!-- Selected positions (wrap downward, fixed layout) -->
          <div
            v-if="selectedJobs.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 rounded-lg"
          >
            <div
              v-for="(job, index) in selectedJobs"
              :key="index"
              class="flex items-center justify-between bg-customButton text-white text-sm px-3 py-1 rounded-full"
            >
              <span class="truncate">{{ job }}</span>
              <button
                @click.stop="removeJob(job)"
                class="text-white hover:text-gray-200 font-bold ml-2"
              >
                ×
              </button>
            </div>
          </div>
        </div>

        <div class="w-full">
          <!-- Input Field -->
          <div>
            <input
              v-model="newSkill"
              @keydown.enter.prevent="addSkill"
              class="input w-full bg-gray-100 focus:outline-none focus:border-transparent"
              type="text"
              placeholder="Type skills here (e.g., Java, Python, Communication)"
            />
          </div>

          <!-- Skill Bubbles -->
          <div
            class="grid grid-cols-[repeat(auto-fill,minmax(120px,1fr))] gap-2 p-3 rounded-lg mb-4 mt-2"
          >
            <div
              v-for="(skill, index) in skills"
              :key="index"
              class="flex items-center justify-between bg-customButton text-white px-3 py-1 text-sm rounded-full overflow-hidden"
            >
              <span class="truncate">{{ skill }}</span>
              <button
                @click="removeSkill(index)"
                class="ml-2 text-white hover:text-gray-200 font-bold"
              >
                ×
              </button>
            </div>

            <!-- Shown if no skills left -->
            <p
              v-if="!skills.length"
              class="text-gray-400 italic text-sm col-span-full"
            >
              No skills added yet.
            </p>
          </div>
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
                @click.prevent="showTermsModal = true"
              >
                Accept all terms and conditions.
              </a>
            </span>
          </label>

          <!-- Modal -->
          <div
            v-if="showTermsModal"
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
                You voluntarily consent to the collection, processing, and
                storage of your personal data by the system. Your personal data
                will be handled in accordance with the Data Privacy Act of 2012
                (Republic Act No. 10173), ensuring its confidentiality,
                integrity, and security. Your personal data will be used solely
                for legitimate purposes related to the services provided by this
                system. You have the right to access, correct, or request
                deletion of your personal data as provided under the law.
                <br /><br />
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

        <div class="card-actions justify-end pt-4 pb-20">
          <button
            type="submit"
            class="btn w-2/4 bg-customButton hover:bg-dark-slate text-white"
            @click="showSkillsModal = true"
          >
            Register
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
      <div
        class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full text-center relative"
      >
        <h2 class="text-lg font-bold text-green-600 mb-4">
          Registration Successful
        </h2>
        <p class="text-sm mb-6">Your account has been created successfully.</p>
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
import { ref, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

// Example skills array (can come from API or props)
const skills = ref(["JavaScript", "Vue.js", "Python", "Communication"]);

function addCustomJob() {
  const trimmed = search.value.trim();
  if (trimmed && !selectedJobs.value.includes(trimmed)) {
    selectedJobs.value.push(trimmed);
  }

  search.value = "";
  showDropdown.value = true;
}

// Input model
const newSkill = ref("");

// Add skill on Enter
const addSkill = () => {
  const skill = newSkill.value.trim();
  if (skill && !skills.value.includes(skill)) {
    skills.value.push(skill);
  }
  newSkill.value = "";
};

const removeSkill = (index) => {
  skills.value.splice(index, 1);
};

const router = useRouter();
const form = ref({
  firstName: "",
  middleName: "",
  lastName: "",
  address: "",
  emailAddress: "",
  phoneNumber: "",
  password: "",
  confirmPassword: "",
  message: "password does not match",
});

const termsAccepted = ref(false);
const showTermsModal = ref(false); // terms modal
const showSuccessModal = ref(false); // ✅ success modal
const showSkillsModal = ref(false); // skills modal

const handleSubmit = async () => {
  if (!termsAccepted.value) {
    alert("You must accept the terms and conditions.");
    return;
  }

  if (form.value.password !== form.value.confirmPassword) {
    alert("Passwords do not match.");
    return;
  }

  try {
    await axios.post("http://127.0.0.1:8000/api/applicants", {
      ...form.value,
    });

    // ✅ Show success modal instead of redirecting immediately
    showSuccessModal.value = true;
  } catch (error) {
    if (error.response && error.response.data.errors) {
      const errors = error.response.data.errors;
      alert(Object.values(errors).flat().join("\n"));
    } else {
      alert("Registration failed. Please try again.");
    }
  }
};

const goToLogin = () => {
  showSuccessModal.value = false;
  router.push("Login");
};

// Two separate toggles
const showPassword = ref(false);
const showConfirm = ref(false);

const search = ref("");
const showDropdown = ref(false);
const selectedJobs = ref([]);
const inputEl = ref(null);

// Example job options (can be replaced later with API data)
const jobOptions = [
  "Software Engineer",
  "Frontend Developer",
  "Backend Developer",
  "Full Stack Developer",
  "UI/UX Designer",
  "Data Analyst",
  "Project Manager",
  "DevOps Engineer",
  "Quality Assurance Tester",
  "System Administrator",
  "Technical Writer",
  "Product Manager",
];

const filteredJobs = computed(() => {
  const term = search.value.toLowerCase();
  return jobOptions.filter((job) => job.toLowerCase().includes(term));
});

function selectJob(job) {
  if (!selectedJobs.value.includes(job)) {
    selectedJobs.value.push(job);
  }
  search.value = "";
  showDropdown.value = true; // keep dropdown open
}

function removeJob(job) {
  selectedJobs.value = selectedJobs.value.filter((j) => j !== job);
}

function hideDropdown() {
  setTimeout(() => (showDropdown.value = false), 150);
}

function focusInput() {
  inputEl.value?.focus();
}
</script>

<style scoped>
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-textfield-decoration-container {
  display: none !important;
}
</style>

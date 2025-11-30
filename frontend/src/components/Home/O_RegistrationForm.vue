<template>
  <div
    class="font-poppins pb-20 min-h-screen flex items-center justify-center p-4"
  >
    <div>
      <div>
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
        <!-- Logo Upload -->
        <div class="form-control mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Organization Logo (Optional)
          </label>
          <div class="flex items-center space-x-4">
            <div v-if="logoPreview" class="flex-shrink-0">
              <img
                :src="logoPreview"
                alt="Logo preview"
                class="h-20 w-20 object-cover rounded-lg border border-gray-300"
              />
            </div>
            <div v-else class="flex-shrink-0">
              <div class="h-20 w-20 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                <svg
                  class="h-8 w-8 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <input
                type="file"
                ref="logoInput"
                accept="image/*"
                @change="handleLogoUpload"
                class="hidden"
                id="logo-upload"
              />
              <label
                for="logo-upload"
                class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                <svg
                  class="h-5 w-5 mr-2 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                {{ logoFile ? logoFile.name : 'Choose Logo' }}
              </label>
              <button
                v-if="logoFile"
                type="button"
                @click="removeLogo"
                class="ml-2 text-sm text-red-600 hover:text-red-800"
              >
                Remove
              </button>
              <p v-if="logoError" class="text-red-500 text-xs mt-1">{{ logoError }}</p>
              <p class="text-gray-500 text-xs mt-1">
                Accepted formats: JPEG, PNG, GIF, WebP (Max 5MB)
              </p>
            </div>
          </div>
        </div>

        <div class="form-control mb-4">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Organization Name*</span>
          </label>
          <input
            class="input w-full bg-gray-100"
            type="text"
            required
            placeholder="Enter organization name"
            name="name"
            v-model="form.name"
          />
        </div>

        <!-- Address Fields - Segmented Dropdowns (shown when API works) -->
        <template v-if="!apiFailed">
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Region*</span>
            </label>
            <select
              class="select w-full bg-gray-100"
              required
              v-model="form.region"
              @change="onRegionChange"
              :disabled="loadingRegions"
            >
              <option value="" disabled>{{ loadingRegions ? 'Loading regions...' : 'Select Region' }}</option>
              <option v-for="region in regions" :key="region.psgc_code" :value="region.psgc_code">
                {{ region.name }}
              </option>
            </select>
          </div>

          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Province*</span>
            </label>
            <select
              class="select w-full bg-gray-100"
              required
              v-model="form.province"
              @change="onProvinceChange"
              :disabled="!form.region || loadingProvinces"
            >
              <option value="" disabled>
                {{ !form.region ? 'Select Region first' : loadingProvinces ? 'Loading provinces...' : 'Select Province' }}
              </option>
              <option v-for="province in provinces" :key="province.psgc_code" :value="province.psgc_code">
                {{ province.name }}
              </option>
            </select>
          </div>

          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">City/Municipality*</span>
            </label>
            <select
              class="select w-full bg-gray-100"
              required
              v-model="form.city"
              @change="onCityChange"
              :disabled="!form.province || loadingCities"
            >
              <option value="" disabled>
                {{ !form.province ? 'Select Province first' : loadingCities ? 'Loading cities...' : 'Select City/Municipality' }}
              </option>
              <option v-for="city in cities" :key="city.psgc_code" :value="city.psgc_code">
                {{ city.name }}
              </option>
            </select>
          </div>

          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Barangay*</span>
            </label>
            <select
              class="select w-full bg-gray-100"
              required
              v-model="form.barangay"
              :disabled="!form.city || loadingBarangays"
            >
              <option value="" disabled>
                {{ !form.city ? 'Select City/Municipality first' : loadingBarangays ? 'Loading barangays...' : 'Select Barangay' }}
              </option>
              <option v-for="barangay in barangays" :key="barangay.psgc_code" :value="barangay.psgc_code">
                {{ barangay.name }}
              </option>
            </select>
          </div>

          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Street Address (Optional)</span>
            </label>
            <input
              class="input w-full bg-gray-100"
              type="text"
              placeholder="House/Building Number, Street Name"
              v-model="form.streetAddress"
            />
          </div>
        </template>

        <!-- Fallback: Manual address input if API fails -->
        <div v-else class="form-control mb-4">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Location*</span>
          </label>
          <input
            class="input w-full bg-gray-100"
            type="text"
            required
            placeholder="Enter your complete address"
            name="location"
            v-model="form.location"
          />
          <p class="text-xs text-gray-500 mt-1">
            Location API is unavailable. Please enter your full address manually.
          </p>
        </div>

        <div class="form-control mb-4">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Website URL (optional)</span>
          </label>
          <input
            class="input w-full bg-gray-100"
            type="url"
            placeholder="Enter website URL"
            name="websiteURL"
            v-model="form.websiteURL"
          />
        </div>

        <div class="form-control mb-4">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Email*</span>
          </label>
          <input
            class="input validator w-full bg-gray-100"
            type="email"
            required
            placeholder="Enter your email address"
            name="emailAddress"
            v-model="form.emailAddress"
          />
          <p class="validator-hint hidden">Invalid Email</p>
        </div>

        <div class="form-control mb-4">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Phone Number</span>
          </label>
          <input
            type="tel"
            class="input validator tabular-nums w-full bg-gray-100"
            placeholder="Enter 11-digit phone number"
            minlength="11"
            maxlength="11"
            pattern="[0-9]*"
            title="Must be 11 digits"
            v-model="form.phoneNumber"
          />
          <p class="hidden validator-hint">Must be 11 digits</p>
        </div>

        <div class="form-control mb-4 relative">
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Password*</span>
          </label>
          <input
            :type="showPassword ? 'text' : 'password'"
            class="input validator w-full pr-10 bg-gray-100"
            required
            placeholder="Enter your password"
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
          <label class="label">
            <span class="label-text text-sm font-medium text-gray-700">Confirm Password*</span>
          </label>
          <!-- Input + toggle wrapper -->
          <div class="relative">
            <input
              :type="showConfirm ? 'text' : 'password'"
              class="input w-full pr-10 focus:outline-none focus:border-transparent bg-gray-100"
              required
              placeholder="Re-enter your password"
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

        <div class="card-actions justify-end pt-4">
          <button
            class="btn w-2/4 bg-customButton hover:bg-dark-slate text-white"
            :disabled="logoUploading"
          >
            {{ logoUploading ? 'Uploading Logo...' : 'Register' }}
          </button>
        </div>
      </form>
    </div>

    <!-- 🔵 Email Verification Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 flex items-center justify-center"
      style="background-color: rgba(0, 0, 0, 0.5); z-index: 9999"
    >
      <div
        class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full text-center relative"
      >
        <div class="mb-4">
          <svg
            class="mx-auto h-16 w-16 text-blue-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
            ></path>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-blue-600 mb-4">
          Verify Your Email Address
        </h2>
        <p class="text-sm mb-4 text-gray-700">
          Your organization account has been created successfully!
        </p>
        <p class="text-sm mb-6 text-gray-600">
          Please check your email (<strong>{{
            registeredEmail || form.emailAddress
          }}</strong
          >) and click the verification link to activate your account.
        </p>
        <div class="space-y-2">
          <button
            class="btn btn-primary w-full bg-dark-slate text-white"
            @click="goToLogin"
          >
            Go to Login
          </button>
          <button
            v-if="registrationResponse?.verification_url"
            class="btn btn-secondary w-full bg-gray-200 text-gray-700 text-xs"
            @click="copyVerificationLink"
          >
            Copy Verification Link
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import api from "../../composables/api.js";
import { uploadImage } from "../../lib/supabase.js";

const router = useRouter();

const form = ref({
  name: "",
  region: "",
  province: "",
  city: "",
  barangay: "",
  streetAddress: "",
  location: "", // Will be constructed from the above fields
  websiteURL: "",
  emailAddress: "",
  phoneNumber: "",
  password: "",
  confirmPassword: "",
  logoPath: "",
});

// Philippines Location API data
const regions = ref([]);
const provinces = ref([]);
const cities = ref([]);
const barangays = ref([]);
const loadingRegions = ref(false);
const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);
const apiFailed = ref(false);

const logoFile = ref(null);
const logoPreview = ref(null);
const logoError = ref("");
const logoInput = ref(null);
const logoUploading = ref(false);

const termsAccepted = ref(false);
const showModal = ref(false);
const showSuccessModal = ref(false);
const registrationResponse = ref(null);
const registeredEmail = ref("");

const handleLogoUpload = async (event) => {
  const file = event?.target?.files?.[0];
  if (!file) return;

  logoError.value = "";

  // Validate file type
  const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
  if (!validImageTypes.includes(file.type)) {
    logoError.value = "Please upload a valid image file (JPEG, PNG, GIF, or WebP).";
    event.target.value = "";
    return;
  }

  // Validate file size (5MB max)
  const MAX_SIZE = 5 * 1024 * 1024; // 5MB
  if (file.size > MAX_SIZE) {
    logoError.value = "Image is too large. Maximum size is 5MB.";
    event.target.value = "";
    return;
  }

  logoFile.value = file;

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeLogo = () => {
  logoFile.value = null;
  logoPreview.value = null;
  logoError.value = "";
  if (logoInput.value) {
    logoInput.value.value = "";
  }
  form.value.logoPath = "";
};

const handleSubmit = async () => {
  if (!termsAccepted.value) {
    return;
  }
  if (form.value.password !== form.value.confirmPassword) {
    return;
  }

  // Validate address fields
  if (!apiFailed.value) {
    if (!form.value.region || !form.value.province || !form.value.city || !form.value.barangay) {
      alert("Please complete all address fields (Region, Province, City/Municipality, and Barangay).");
      return;
    }
    // Construct full address from selected fields
    form.value.location = constructAddress();
  } else {
    // If API failed, use manual address input
    if (!form.value.location || form.value.location.trim() === "") {
      alert("Please enter your complete address.");
      return;
    }
  }

  try {
    // Upload logo if provided
    if (logoFile.value) {
      logoUploading.value = true;
      logoError.value = "";
      try {
        const logoPath = await uploadImage(logoFile.value, "Requirements", "org_logo_directory");
        if (logoPath) {
          form.value.logoPath = logoPath;
        } else {
          logoError.value = "Failed to upload logo. Please try again.";
          logoUploading.value = false;
          return;
        }
      } catch (error) {
        console.error("Error uploading logo:", error);
        logoError.value = "Failed to upload logo. Please try again.";
        logoUploading.value = false;
        return;
      } finally {
        logoUploading.value = false;
      }
    }

    const { confirmPassword, ...payload } = form.value;
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/organization",
      payload
    );

    console.log("Registration response:", response);
    console.log("Response status:", response.status);
    console.log("Response data:", response.data);

    // Store registration response (handle both direct data and nested organization property)
    registrationResponse.value = response.data;
    // Extract verification_url if it exists in the response
    if (response.data.verification_url) {
      registrationResponse.value.verification_url =
        response.data.verification_url;
    }
    registeredEmail.value = form.value.emailAddress;

    // ✅ Show email verification modal
    console.log("Setting showSuccessModal to true");
    showSuccessModal.value = true;
    console.log("showSuccessModal value:", showSuccessModal.value);

    // Force Vue to update
    await nextTick();
    console.log("After nextTick, showSuccessModal:", showSuccessModal.value);

    // ✅ Clear form
    form.value = {
      name: "",
      region: "",
      province: "",
      city: "",
      barangay: "",
      streetAddress: "",
      location: "",
      websiteURL: "",
      emailAddress: "",
      phoneNumber: "",
      password: "",
      confirmPassword: "",
      logoPath: "",
    };
    regions.value = [];
    provinces.value = [];
    cities.value = [];
    barangays.value = [];
    removeLogo();
    termsAccepted.value = false;
  } catch (error) {
    console.error("Registration error:", error);
    console.error("Error response:", error.response);
    console.error("Error data:", error.response?.data);

    // Check if it's actually a success (201 status) but axios is treating it as error
    if (
      error.response?.status === 201 ||
      (error.response?.status >= 200 && error.response?.status < 300)
    ) {
      // Registration actually succeeded
      registrationResponse.value =
        error.response.data.organization || error.response.data;
      registeredEmail.value = form.value.emailAddress;
      showSuccessModal.value = true;

      // Clear form
      form.value = {
        name: "",
        region: "",
        province: "",
        city: "",
        barangay: "",
        streetAddress: "",
        location: "",
        websiteURL: "",
        emailAddress: "",
        phoneNumber: "",
        password: "",
        confirmPassword: "",
        logoPath: "",
      };
      regions.value = [];
      provinces.value = [];
      cities.value = [];
      barangays.value = [];
      removeLogo();
      termsAccepted.value = false;
      return;
    }

    // Show validation errors to user
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const errors = error.response.data.errors;
      let errorMessage = "Validation errors:\n";

      Object.keys(errors).forEach((key) => {
        errorMessage += `${key}: ${errors[key].join(", ")}\n`;
      });

      alert(errorMessage);
    } else {
      alert(
        error.response?.data?.message ||
          "Registration failed. Please try again."
      );
    }
  }
};

const goToLogin = () => {
  showSuccessModal.value = false;
  router.push("Login");
};

const copyVerificationLink = () => {
  if (registrationResponse.value?.verification_url) {
    navigator.clipboard
      .writeText(registrationResponse.value.verification_url)
      .then(() => {
        alert("Verification link copied to clipboard!");
      })
      .catch(() => {
        alert(
          "Failed to copy link. Please copy manually:\n" +
            registrationResponse.value.verification_url
        );
      });
  }
};

const showPassword = ref(false);
const showConfirm = ref(false);

async function fetchRegionsPSGC() {
  loadingRegions.value = true;
  try {
    const response = await axios.get("https://psgc.gitlab.io/api/regions.json");
    regions.value = (response.data || []).map(region => ({
      psgc_code: region.code || region.psgc_code,
      name: region.name
    }));
    apiFailed.value = false;
  } catch (error) {
    console.error("PSGC API failed, will fallback to GitHub JSON:", error);
    apiFailed.value = true;
    // Optional: call your existing fetchRegions() as fallback
    await fetchRegions(); // your existing function
  } finally {
    loadingRegions.value = false;
  }
}

// On mount, use PSGC API first
onMounted(async () => {
  await fetchRegionsPSGC();
});

// Philippines Location API - Using multiple reliable sources
const PH_LOCATION_API_BASE = "https://raw.githubusercontent.com/iamkevinluke/philippines-regions-provinces-cities-municipalities-barangays/master";

// Fetch regions on mount
async function fetchRegions() {
  loadingRegions.value = true;
  try {
    // Try primary source: iamkevinluke's repository
    const response = await axios.get(`${PH_LOCATION_API_BASE}/regions.json`);
    regions.value = (response.data || []).map(region => ({
      psgc_code: region.code || region.psgc_code || region.id || region.region_code,
      name: region.name || region.region_name || region.regionName
    }));
  } catch (error) {
    console.error("Error fetching regions:", error);
    // Fallback 1: Try alternative GitHub repository
    try {
      const fallbackResponse = await axios.get("https://raw.githubusercontent.com/simonbengtsson/jsondata/master/philippines/regions.json");
      regions.value = (fallbackResponse.data || []).map(region => ({
        psgc_code: region.code || region.id,
        name: region.name
      }));
    } catch (fallbackError1) {
      console.error("Error fetching regions from fallback 1:", fallbackError1);
      // Fallback 2: Try PSGC API
      try {
        const fallbackResponse2 = await axios.get("https://psgc.gitlab.io/api/regions.json");
        regions.value = (fallbackResponse2.data || []).map(region => ({
          psgc_code: region.code || region.psgc_code,
          name: region.name
        }));
      } catch (fallbackError2) {
        console.error("Error fetching regions from all sources:", fallbackError2);
        // Last resort: Use a minimal hardcoded list of major regions
        regions.value = [
          { psgc_code: "010000000", name: "Ilocos Region (Region I)" },
          { psgc_code: "020000000", name: "Cagayan Valley (Region II)" },
          { psgc_code: "030000000", name: "Central Luzon (Region III)" },
          { psgc_code: "040000000", name: "CALABARZON (Region IV-A)" },
          { psgc_code: "050000000", name: "MIMAROPA (Region IV-B)" },
          { psgc_code: "060000000", name: "Bicol Region (Region V)" },
          { psgc_code: "070000000", name: "Western Visayas (Region VI)" },
          { psgc_code: "080000000", name: "Central Visayas (Region VII)" },
          { psgc_code: "090000000", name: "Eastern Visayas (Region VIII)" },
          { psgc_code: "100000000", name: "Zamboanga Peninsula (Region IX)" },
          { psgc_code: "110000000", name: "Northern Mindanao (Region X)" },
          { psgc_code: "120000000", name: "Davao Region (Region XI)" },
          { psgc_code: "130000000", name: "SOCCSKSARGEN (Region XII)" },
          { psgc_code: "140000000", name: "Caraga (Region XIII)" },
          { psgc_code: "150000000", name: "Bangsamoro (BARMM)" },
          { psgc_code: "160000000", name: "Cordillera Administrative Region (CAR)" },
          { psgc_code: "170000000", name: "National Capital Region (NCR)" }
        ];
        // If even hardcoded list fails to load, show manual input
        if (regions.value.length === 0) {
          apiFailed.value = true;
        }
      }
    }
  } finally {
    loadingRegions.value = false;
  }
}

// Fetch provinces based on selected region
async function onRegionChange() {
  if (!form.value.region) return;
  
  form.value.province = "";
  form.value.city = "";
  form.value.barangay = "";
  provinces.value = [];
  cities.value = [];
  barangays.value = [];
  
  loadingProvinces.value = true;
  try {
    const response = await axios.get(`${PH_LOCATION_API_BASE}/provinces.json`);
    const allProvinces = response.data || [];
    provinces.value = allProvinces
      .filter(p => {
        const regionCode = p.region_code || p.regionCode || p.region?.code || p.region?.psgc_code || p.region_id;
        return regionCode === form.value.region || regionCode?.toString() === form.value.region?.toString();
      })
      .map(province => ({
        psgc_code: province.code || province.psgc_code || province.province_code || province.id,
        name: province.name || province.province_name,
        region_code: province.region_code || province.regionCode || province.region_id
      }));
  } catch (error) {
    console.error("Error fetching provinces:", error);
    // Fallback: Try PSGC API
    try {
      const fallbackResponse = await axios.get(`https://psgc.gitlab.io/api/regions/${form.value.region}/provinces.json`);
      provinces.value = (fallbackResponse.data || []).map(province => ({
        psgc_code: province.code || province.psgc_code,
        name: province.name,
        region_code: province.region_code
      }));
    } catch (fallbackError) {
      console.error("Error fetching provinces from fallback:", fallbackError);
      provinces.value = [];
    }
  } finally {
    loadingProvinces.value = false;
  }
}

// Fetch cities/municipalities based on selected province
async function onProvinceChange() {
  if (!form.value.province) return;
  
  form.value.city = "";
  form.value.barangay = "";
  cities.value = [];
  barangays.value = [];
  
  loadingCities.value = true;
  try {
    const response = await axios.get(`${PH_LOCATION_API_BASE}/cities.json`);
    const allCities = response.data || [];
    cities.value = allCities
      .filter(c => {
        const provinceCode = c.province_code || c.provinceCode || c.province?.code || c.province?.psgc_code || c.province_id;
        return provinceCode === form.value.province || provinceCode?.toString() === form.value.province?.toString();
      })
      .map(city => ({
        psgc_code: city.code || city.psgc_code || city.city_code || city.id,
        name: city.name || city.city_name,
        province_code: city.province_code || city.provinceCode || city.province_id
      }));
  } catch (error) {
    console.error("Error fetching cities:", error);
    // Fallback: Try PSGC API
    try {
      const fallbackResponse = await axios.get(`https://psgc.gitlab.io/api/provinces/${form.value.province}/cities-municipalities.json`);
      cities.value = (fallbackResponse.data || []).map(city => ({
        psgc_code: city.code || city.psgc_code,
        name: city.name,
        province_code: city.province_code
      }));
    } catch (fallbackError) {
      console.error("Error fetching cities from fallback:", fallbackError);
      cities.value = [];
    }
  } finally {
    loadingCities.value = false;
  }
}

// Fetch barangays based on selected city
async function onCityChange() {
  if (!form.value.city) return;
  
  form.value.barangay = "";
  barangays.value = [];
  
  loadingBarangays.value = true;
  try {
    const response = await axios.get(`${PH_LOCATION_API_BASE}/barangays.json`);
    const allBarangays = response.data || [];
    barangays.value = allBarangays
      .filter(b => {
        const cityCode = b.city_code || b.cityCode || b.city?.code || b.city?.psgc_code || b.municipality_code || b.municipalityCode || b.city_municipality_code;
        return cityCode === form.value.city || cityCode?.toString() === form.value.city?.toString();
      })
      .map(barangay => ({
        psgc_code: barangay.code || barangay.psgc_code || barangay.barangay_code || barangay.id,
        name: barangay.name || barangay.barangay_name,
        city_code: barangay.city_code || barangay.cityCode || barangay.municipality_code || barangay.city_municipality_code
      }));
  } catch (error) {
    console.error("Error fetching barangays:", error);
    // Fallback: Try PSGC API
    try {
      const fallbackResponse = await axios.get(`https://psgc.gitlab.io/api/cities-municipalities/${form.value.city}/barangays.json`);
      barangays.value = (fallbackResponse.data || []).map(barangay => ({
        psgc_code: barangay.code || barangay.psgc_code,
        name: barangay.name,
        city_code: barangay.city_municipality_code
      }));
    } catch (fallbackError) {
      console.error("Error fetching barangays from fallback:", fallbackError);
      barangays.value = [];
    }
  } finally {
    loadingBarangays.value = false;
  }
}

// Construct full address string from selected fields
function constructAddress() {
  const parts = [];
  
  if (form.value.streetAddress) {
    parts.push(form.value.streetAddress);
  }
  
  const selectedBarangay = barangays.value.find(b => b.psgc_code === form.value.barangay);
  if (selectedBarangay) {
    parts.push(selectedBarangay.name);
  }
  
  const selectedCity = cities.value.find(c => c.psgc_code === form.value.city);
  if (selectedCity) {
    parts.push(selectedCity.name);
  }
  
  const selectedProvince = provinces.value.find(p => p.psgc_code === form.value.province);
  if (selectedProvince) {
    parts.push(selectedProvince.name);
  }
  
  const selectedRegion = regions.value.find(r => r.psgc_code === form.value.region);
  if (selectedRegion) {
    parts.push(selectedRegion.name);
  }
  
  return parts.join(", ");
}

// Fetch regions on component mount
onMounted(async () => {
  await fetchRegions();
});
</script>

<style scoped>
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear,
input[type="password"]::-webkit-textfield-decoration-container {
  display: none !important;
}
</style>

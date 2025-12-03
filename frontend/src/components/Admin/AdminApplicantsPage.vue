<template>
  <div class="min-h-screen p-6 bg-gray-50 font-poppins">
    <!-- Header -->
    <header class="mb-6">
      <h1 class="text-3xl font-bold">Applicant's List</h1>
    </header>

    <!-- Applicants List -->
    <section>
        <!-- Loading State -->
        <div v-if="loading" class="flex flex-col items-center justify-center space-y-2 py-10">
          <svg
            class="animate-spin h-10 w-10 text-blue-600"
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
              d="M4 12a8 8 0 018-8v8H4z"
            ></path>
          </svg>
          <p class="text-gray-500">Loading applicants...</p>
        </div>

        <!-- No applicants -->
        <div v-else-if="applicants.length === 0">
          <p class="text-gray-500 italic">No applicants found.</p>
        </div>

        <!-- Applicants List -->
        <div v-else class="space-y-3">
          <div
            v-for="applicant in applicants"
            :key="applicant.applicantID"
            @click="openApplicantModal(applicant)"
            class="p-3 border rounded-lg bg-blue-50 flex justify-between items-center cursor-pointer hover:bg-blue-100 transition"
          >
            <div>
              <h3 class="font-semibold">
                {{ applicant.firstName }} {{ applicant.middleName ?? '' }} {{ applicant.lastName }}
              </h3>
              <p class="text-sm text-gray-600">{{ applicant.emailAddress }}</p>
              <p class="text-sm text-gray-600">{{ applicant.phoneNumber }}</p>
              <p class="text-sm text-gray-600">{{ applicant.address }}</p>
            </div>

            <span class="px-3 py-1 bg-blue-600 text-white rounded-lg text-sm">
              Applicant
            </span>
          </div>
        </div>
      </section>

    <!-- Applicant Details Modal -->
    <div
      v-if="selectedApplicant"
      class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-md"
      @click.self="selectedApplicant = null"
    >
      <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
        <button
          @click="selectedApplicant = null"
          class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl font-bold"
        >
          ✕
        </button>

        <h2 class="text-2xl font-semibold mb-4">Applicant Details</h2>

        <div class="space-y-4">
          <!-- Personal Information -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-800">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <p class="text-sm text-gray-600">Full Name</p>
                <p class="font-medium">
                  {{ selectedApplicant.firstName }} 
                  {{ selectedApplicant.middleName ? selectedApplicant.middleName + ' ' : '' }}
                  {{ selectedApplicant.lastName }}
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Email Address</p>
                <p class="font-medium">{{ selectedApplicant.emailAddress }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Phone Number</p>
                <p class="font-medium">{{ selectedApplicant.phoneNumber || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600">Address</p>
                <p class="font-medium">{{ selectedApplicant.address || 'N/A' }}</p>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div v-if="selectedApplicant.dateOfBirth || selectedApplicant.gender" class="border-b pb-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-800">Additional Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div v-if="selectedApplicant.dateOfBirth">
                <p class="text-sm text-gray-600">Date of Birth</p>
                <p class="font-medium">{{ formatDate(selectedApplicant.dateOfBirth) }}</p>
              </div>
              <div v-if="selectedApplicant.gender">
                <p class="text-sm text-gray-600">Gender</p>
                <p class="font-medium">{{ selectedApplicant.gender }}</p>
              </div>
            </div>
          </div>

          <!-- Account Information -->
          <div class="border-b pb-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-800">Account Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <p class="text-sm text-gray-600">Applicant ID</p>
                <p class="font-medium">{{ selectedApplicant.applicantID }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const applicants = ref([]);
const loading = ref(true);
const selectedApplicant = ref(null);

async function loadApplicants() {
  try {
    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/admin/applicants");
    applicants.value = res.data;
  } catch (err) {
    console.error("Error loading applicants:", err);
  } finally {
    loading.value = false;
  }
}

function openApplicantModal(applicant) {
  selectedApplicant.value = applicant;
}

function formatDate(dateString) {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}

onMounted(() => {
  loadApplicants();
});
</script>

<style scoped>
/* Optional: spacing, font, and layout */
</style>
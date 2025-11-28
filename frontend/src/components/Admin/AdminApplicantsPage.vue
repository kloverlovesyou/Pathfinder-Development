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
            class="p-3 border rounded-lg bg-blue-50 flex justify-between items-center"
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
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const applicants = ref([]);
const loading = ref(true);

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

onMounted(() => {
  loadApplicants();
});
</script>

<style scoped>
/* Optional: spacing, font, and layout */
</style>
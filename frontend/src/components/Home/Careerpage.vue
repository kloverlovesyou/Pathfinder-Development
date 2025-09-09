<script setup>
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";
import axios from "axios";

const router = useRouter();
const careers = ref([
  {
    id: 3,
    type: "career",
    position: "Software Engineer",
    deadlineOfSubmission: "July 24, 2025",
    organizationImage: "https://via.placeholder.com/150",
  },
]);

onMounted(async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/careers");
    careers.value = response.data;
  } catch (error) {
    console.error("Error fetching careers:", error);
  }
});
</script>

<template>
  <main>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-4">
      <!-- Title -->
      <h2 class="text-2xl font-bold -m-2">Career</h2>
    </div>

    <!-- Career Cards -->
    <div class="space-y-4">
      <div
        v-for="career in careers"
        :key="career.id"
        class="p-4 bg-blue-gray rounded-lg relative hover:bg-gray-300 transition"
      >
        <!-- Dropdown (top-right inside card) -->
        <div class="absolute top-2 right-2 z-50">
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
            </div>
            <ul
              tabindex="0"
              class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-md mt-0"
            >
              <li><a>Bookmark</a></li>
              <li><a>Apply</a></li>
            </ul>
          </div>
        </div>

        <!-- Router-link wraps only the card content -->
        <router-link
          :to="{ name: 'CareerDetails', params: { id: career.id } }"
          class="block"
        >
          <h3 class="font-semibold text-lg">{{ career.position }}</h3>
          <p class="text-gray-600 text-sm">
            Deadline: {{ career.deadlineOfSubmission }}
          </p>
        </router-link>
      </div>
    </div>
  </main>
</template>

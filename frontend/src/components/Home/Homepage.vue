<script setup>
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";
import axios from "axios";
import "cally";

const router = useRouter();
const posts = ref([
  {
    id: 1,
    type: "training",
    title: "Professional development in emerging technologies",
    schedule: "November 24, 2025 | 1:30 PM - 5:00 PM",
  },
  {
    id: 2,
    type: "training",
    title: "Mind Over Machine: Navigating AI in Everyday Life",
    schedule: "September 20, 2025 | 7:30 PM - 12:00 PM",
  },
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
    const careers = await axios.get("http://127.0.0.1:8000/api/careers");
    const trainings = await axios.get("http://127.0.0.1:8000/api/trainings");

    posts.value = [...careers.data, ...trainings.data];
  } catch (error) {
    console.error("Error fetching posts:", error);
  }
});

// Navigate to details page
const goToDetails = (post) => {
  if (post.type === "career") {
    router.push(`/career/${post.id}`);
  } else {
    router.push(`/training/${post.id}`);
  }
};
</script>

<template>
  <main>
    <h2 class="text-lg font-bold -mb-1">Career & Training</h2>
    <h2 class="text-2xl font-bold mb-4">Match Recommendation</h2>
    <div class="space-y-4">
      <div
        v-for="post in posts"
        :key="post.id"
        class="p-4 bg-blue-gray rounded-lg relative cursor-pointer hover:bg-gray-300 transition"
        @click="goToDetails(post)"
      >
        <!-- Dropdown (ellipsis) -->
        <div class="absolute top-2 right-2" @click.stop>
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
              class="dropdown-content menu bg-base-100 rounded-box z-50 w-40 p-2 shadow-md mt-0"
              style="top: 0; right: 100%"
            >
              <li><a>Bookmark</a></li>
              <li>
                <a>{{ post.type === "career" ? "Apply" : "Register" }}</a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Post Info -->
        <div>
          <h3 class="font-semibold text-lg">
            {{ post.title || post.position }}
          </h3>
          <p class="text-gray-600 text-sm">
            {{ post.deadlineOfSubmission || post.schedule }}
          </p>
        </div>
      </div>
    </div>
  </main>
</template>

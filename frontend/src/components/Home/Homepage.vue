<script setup>
import "cally";
import organizationImage from "@/assets/images/org.jpg";
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";

const router = useRouter();
const user = ref(null);

onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    user.value = JSON.parse(storedUser);
  } else {
    router.push("/loginform");
  }
});
</script>

<template>
  <main>

    <h2 class="text-lg font-bold -mb-1">Career & Training</h2>
    <h2 class="text-2xl font-bold mb-4">Match Recommendation</h2>
    <div class="space-y-4">
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Professional development in emerging technologies and cognitive
            enhancement
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
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
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">
          November 24, 2025 | 1:30 PM to 5:00 PM
        </p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Mind Over Machine: Navigating AI in Everyday Life
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
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
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">
          September 20, 2025 | 7:30 PM to 12:00 PM
        </p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Elevate Your Career: Professional Development Workshop Series
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
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
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>
        <p class="text-gray-600 text-sm">July 24, 2025 | 1:30 PM to 5:00 PM</p>
        <div class="mb-4">
          <figure>
            <img :src="organizationImage" alt="Organization" />
          </figure>
        </div>
      </div>
    </div>
  </main>
</template>

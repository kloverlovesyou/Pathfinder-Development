<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const route = useRoute();
const router = useRouter();
const training = ref({
  id: 2,
  title: "Mind Over Machine: Navigating AI in Everyday Life",
  schedule: "2025-09-20 19:30:00",
  description: `This training is designed to help participants understand how artificial intelligence 
is shaping our everyday decisions, workplaces, and communities. Through interactive sessions, 
we will explore the fundamentals of AI, its opportunities, and its challenges.  

The program covers real-world applications of AI, such as recommendation systems, chatbots, 
and automation tools, and how they influence our choices and productivity. Participants will 
also gain insights into ethical considerations, including bias, data privacy, and responsible use 
of technology.  

By the end of the training, attendees will not only recognize the role of AI in their daily lives 
but also develop practical strategies to critically engage with these technologies. Whether you 
are a student, a professional, or simply curious about technology, this session equips you with 
the knowledge to thrive in a digital-first world.`,
  organization: {
    id: 1,
    name: "Tech Academy",
    image: "https://via.placeholder.com/50",
  },
});
const loading = ref(true);

const isClicked = ref(false);

function toggleIcon() {
  isClicked.value = !isClicked.value;
}

onMounted(async () => {
  try {
    const id = route.params.id;
    const response = await axios.get(
      `http://127.0.0.1:8000/api/trainings/${id}`
    );
    training.value = response.data;
  } catch (error) {
    console.error("Error fetching training:", error);
  } finally {
    loading.value = false;
  }
});

const goBack = () => {
  router.push("/trainingpage"); // or the combined page if you want
};

// sample function for register button
const registerTraining = () => {
  alert(`Registered for: ${training.value.title}`);
};
</script>

<template>
  <main class="p-6 font-poppins">
    <button class="group" @click="goBack">
      <!-- Back Arrow -->
      <svg
        class="size-6"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
          stroke="#6682A3"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M20.5 12H3.66998"
          stroke="#6682A3"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </button>

    <div v-if="loading">Loading...</div>

    <div v-else-if="training" class="p-6 relative">
      <!-- Top Row: Org Image, Name & Dropdown -->
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-3">
          <img
            :src="
              training.organization?.image || 'https://via.placeholder.com/50'
            "
            alt="Org Logo"
            class="w-10 h-10 rounded-full object-cover"
          />
          <p class="font-medium text-gray-800">
            {{ training.organization?.name || "Unknown Organization" }}
          </p>
        </div>

        <!-- Ellipsis Dropdown -->
        <div class="dropdown dropdown-left lg:hidden block">
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
          >
            <li><a>Bookmark</a></li>
<<<<<<< HEAD
            <li><a>Register</a></li>
=======
            <a>Apply</a>
>>>>>>> 963aa44b98712c6d8afb28517c93b4a6ecac003e
          </ul>
        </div>
      </div>

      <!-- Training Title -->
      <h1 class="text-2xl font-bold mb-2">{{ training.title }}</h1>

      <!-- Schedule -->
      <p class="text-gray-600 mb-2">{{ training.schedule }}</p>

      <!-- Register Button -->
      <div class="flex gap-2 justify-end">
        <button
          @click="registerTraining"
          class="btn btn-primary bg-customButton hover:bg-dark-slate text-white hidden lg:block"
        >
          Register
        </button>
        <button class="rounded-lg hidden lg:block" @click="toggleIcon">
          <svg
            v-if="!isClicked"
            class="size-12 flex-shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16.82 2H7.18001C5.05001 2 3.32001 3.74 3.32001 5.86V19.95C3.32001 21.75 4.61001 22.51 6.19001 21.64L11.07 18.93C11.59 18.64 12.43 18.64 12.94 18.93L17.82 21.64C19.4 22.52 20.69 21.76 20.69 19.95V5.86C20.68 3.74 18.95 2 16.82 2Z"
              stroke="#6682A3"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M9.59003 11L11.09 12.5L15.09 8.5"
              stroke="#6682A3"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <svg
            v-else
            class="size-12 flex-shrink-0"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M16.82 1.90991H7.18001C5.06001 1.90991 3.32001 3.64991 3.32001 5.76991V19.8599C3.32001 21.6599 4.61001 22.4199 6.19001 21.5499L11.07 18.8399C11.59 18.5499 12.43 18.5499 12.94 18.8399L17.82 21.5499C19.4 22.4299 20.69 21.6699 20.69 19.8599V5.76991C20.68 3.64991 18.95 1.90991 16.82 1.90991ZM15.62 9.02991L11.62 13.0299C11.47 13.1799 11.28 13.2499 11.09 13.2499C10.9 13.2499 10.71 13.1799 10.56 13.0299L9.06001 11.5299C8.77001 11.2399 8.77001 10.7599 9.06001 10.4699C9.35001 10.1799 9.83001 10.1799 10.12 10.4699L11.09 11.4399L14.56 7.96991C14.85 7.67991 15.33 7.67991 15.62 7.96991C15.91 8.25991 15.91 8.73991 15.62 9.02991Z"
              fill="#6682A3"
            />
          </svg>
        </button>
      </div>
      <div class="divider"></div>
      <!-- Description -->
      <p class="text-gray-700 leading-relaxed mb-4">
        {{ training.description }}
      </p>
    </div>

    <div v-else>
      <p class="text-red-500">Training not found.</p>
    </div>
  </main>
</template>

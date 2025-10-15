<script setup>
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";
import axios from "axios";

const router = useRouter();

// State variables
const userName = ref("");
const token = ref("");
const user = ref(null);
const bookmarks = ref([]);

// ✅ Load user and token from localStorage + fetch bookmarks
onMounted(async () => {
  const savedUser = localStorage.getItem("user");
  const savedToken = localStorage.getItem("token");

  if (savedUser) {
    user.value = JSON.parse(savedUser);
    const { firstName, lastName } = user.value;
    userName.value = firstName && lastName ? `${firstName} ${lastName}` : "Guest";
  } else {
    userName.value = "Guest";
  }

  if (savedToken) {
    token.value = savedToken;
    axios.defaults.headers.common["Authorization"] = `Bearer ${savedToken}`;
    axios.defaults.headers.common["Accept"] = "application/json";
    await loadBookmarks(); // ✅ Load data after setting header
  }
});

// ✅ Fetch bookmarks (Option A: use nested training data)
const loadBookmarks = async () => {
  try {
    const { data } = await axios.get("http://127.0.0.1:8000/api/bookmarks");

    // If API returns [1,2,3], transform it properly
    bookmarks.value = data.map(id => ({
      trainingID: id,
      training: {
        title: "Loading...",
        schedule: "Fetching...",
      },
    }));

    // Optionally fetch training details for each ID
    for (let i = 0; i < bookmarks.value.length; i++) {
      const id = bookmarks.value[i].trainingID;
      const res = await axios.get(`http://127.0.0.1:8000/api/trainings`);
      const t = res.data.find(t => t.trainingID === id);
      if (t) bookmarks.value[i].training = t;
    }

    console.log("Bookmarks loaded:", bookmarks.value);
  } catch (error) {
    console.error("Failed to load bookmarks:", error.response?.data || error);
  }
};

// ✅ Remove a bookmark
const removeBookmark = async (trainingID) => {
  try {
    await axios.delete(`http://127.0.0.1:8000/api/bookmarks/${trainingID}`);
    bookmarks.value = bookmarks.value.filter(b => b.trainingID !== trainingID);
    alert("✅ Bookmark removed successfully!");
  } catch (error) {
    console.error("Error removing bookmark:", error.response?.data || error);
    alert("❌ Failed to remove bookmark.");
  }
};

// ✅ Logout function
const logout = () => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Loginpage" });
};
</script>

<template>
  <main class="font-poppins">
    <div class="min-h-screen p-3 rounded-lg font-poppins">
      <div class="min-h-screen font-poppins lg:flex">
        <!-- ✅ Left Sidebar -->
        <div class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center hidden lg:flex">
          <div class="w-24 h-24 rounded-full bg-white mb-4">
            <img
              src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
              alt="User Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
          <h2 class="text-xl font-semibold mb-6">{{ userName }}</h2>

          <div class="w-full flex flex-col gap-3">
            <button
              class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
              @click="$router.push({ name: 'ResumeEditorpage' })"
            >
              <span>Resume</span>
            </button>
            <button
              class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
              @click="$router.push({ name: 'Certificatespage' })"
            >
              <span>Certificates</span>
            </button>
            <button
              class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
              @click="$router.push({ name: 'Bookmarkpage' })"
            >
              <span>Bookmark</span>
            </button>
            <button
              class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
              @click="logout"
            >
              <span>Logout</span>
            </button>
          </div>
        </div>

        <!-- ✅ Right Content (Bookmarks) -->
        <div class="w-full lg:w-3/4 lg:pl-6 flex flex-col gap-6">
          <div class="bg-white p-4 rounded-lg">
            <h2 class="text-2xl font-bold mb-3">Bookmarked Trainings</h2>

            <!-- ✅ Bookmarks List -->
            <div v-if="bookmarks.length > 0" class="space-y-4">
              <div
                v-for="bookmark in bookmarks"
                :key="bookmark.trainingBookmarkID"
                class="p-4 bg-blue-gray rounded-lg"
              >
                <div class="flex justify-between items-start">
                  <h3 class="font-semibold text-lg">
                    {{ bookmark.training.title }}
                  </h3>

                  <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost m-1">
                      ⋮
                    </div>
                    <ul
                      tabindex="0"
                      class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
                    >
                      <li>
                        <a @click.prevent="removeBookmark(bookmark.trainingID)">
                          Remove bookmark
                        </a>
                      </li>
                      <li><a>Apply</a></li>
                    </ul>
                  </div>
                </div>

                <p class="text-gray-600 text-sm mt-1">
                  {{ bookmark.training.schedule }}
                </p>
              </div>
            </div>

            <!-- ✅ Empty State -->
            <div v-else class="text-center text-gray-500 py-10">
              No bookmarked trainings yet.
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
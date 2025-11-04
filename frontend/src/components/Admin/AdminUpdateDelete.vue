<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <!--Large screen-->
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Right Column -->
      <div class="w-full lg:mt-0 lg:ml-3 flex flex-col gap-6">
        <!-- Bottom Row: Event List -->
        <div class="bg-white rounded-lg shadow p-6 flex-1">
          <header
            class="sticky top-0 z-10 bg-white h-16 text-black flex items-center px-4 shadow"
          >
            <h1 class="text-3xl font-bold">Account Setting</h1>
          </header>

          <!-- FORM -->
          <form @submit.prevent="handleUpdate" class="space-y-4 pt-4">
            <div class="divider sm:hidden"></div>

            <!-- First, Middle, Last Name -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <input
                class="border border-gray-300 input w-full"
                type="text"
                placeholder="Name"
                v-model="form.name"
              />
              <input
                class="border border-gray-300 input w-full"
                type="URL"
                placeholder="Website URL"
                v-model="form.websiteURL"
              />
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <!-- Email & Phone -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <input
                class="border border-gray-300 input w-full"
                type="text"
                placeholder="Location"
                v-model="form.location"
              />
              <input
                class="border border-gray-300 input w-full text-gray-600"
                type="email"
                placeholder="Email"
                v-model="form.emailAddress"
                readonly
              />
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <!-- New & Confirm Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <input
                type="password"
                class="border border-gray-300 input w-full"
                placeholder="New Password (optional)"
                v-model="form.newPassword"
              />
              <input
                type="password"
                class="border border-gray-300 input w-full"
                placeholder="Confirm New Password"
                v-model="form.confirmPassword"
              />
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <div>
              <input
                type="password"
                class="border border-gray-300 input w-full"
                placeholder="Confirm Password"
                v-model="form.currentPassword"
                required
              />
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <!-- Buttons -->
            <div class="flex justify-end pt-6">
              <button
                type="submit"
                class="btn bg-customButton hover:bg-dark-slate text-white w-full sm:w-auto"
              >
                Save Changes
              </button>
            </div>
          </form>

          <!-- Delete Confirmation Modal -->
          <div
            v-if="showDeleteModal"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
          >
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
              <h2 class="text-lg font-semibold mb-4">
                Confirm Account Deletion
              </h2>
              <p class="mb-4 text-sm">
                This action is irreversible. Are you sure you want to delete
                your account?
              </p>
              <div class="flex justify-end gap-2">
                <button
                  class="btn btn-sm"
                  @click="showDeleteModal = false"
                  :disabled="isDeleting"
                >
                  Cancel
                </button>
                <button
                  class="btn btn-error btn-sm text-white"
                  @click="handleDelete"
                  :disabled="isDeleting"
                >
                  {{ isDeleting ? "Deleting..." : "Delete" }}
                </button>
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
import { useRouter } from "vue-router";

const router = useRouter();
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const upcomingCount = ref(0);
const completedCount = ref(0);

const form = ref({
  firstName: "",
  middleName: "",
  lastName: "",
  address: "",
  emailAddress: "",
  phoneNumber: "",
  newPassword: "",
  currentPassword: "",
});

const userName = ref("");

onMounted(async () => {
  fetchTrainingCounters();
  try {
    // --- Fetch user from API ---
    const res = await axios.get("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
    });

    const user = res.data;

    // Update form (including middle name)
    form.value = {
      ...form.value,
      firstName: user.firstName || user.first_name || "",
      middleName: user.middleName || user.middle_name || "",
      lastName: user.lastName || user.last_name || "",
      emailAddress: user.emailAddress || user.email || "",
      phoneNumber: user.phoneNumber || user.phone || "",
      address: user.address || "",
    };

    // Set userName (display only first + last)
    userName.value = `${form.value.firstName} ${form.value.lastName}`.trim();

    // Save backup
    localStorage.setItem("user", JSON.stringify(user));
  } catch (err) {
    console.error("API failed, fallback to localStorage:", err);

    const savedUser = localStorage.getItem("user");
    if (savedUser) {
      const user = JSON.parse(savedUser);

      form.value = {
        ...form.value,
        firstName: user.firstName || user.first_name || "",
        middleName: user.middleName || user.middle_name || "",
        lastName: user.lastName || user.last_name || "",
        emailAddress: user.emailAddress || user.email || "",
        phoneNumber: user.phoneNumber || user.phone || "",
        address: user.address || "",
      };

      userName.value = `${form.value.firstName} ${form.value.lastName}`.trim();
    }
  }
});

function deleteAccount() {
  axios
    .delete("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
    })
    .then((res) => {
      alert(res.data.message);

      // clear saved data
      localStorage.removeItem("token");
      localStorage.removeItem("user");

      // redirect to login
      router.push("/loginform");
    })
    .catch((err) => {
      console.error(err);
      alert("Failed to delete account.");
    });
}

// Fetch TrainingCounter
async function fetchTrainingCounters() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const response = await axios.get(
      "https://pathfinder-development-production.up.railway.app/api/registrations",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    const trainings = response.data || [];

    upcomingCount.value = trainings.filter(
      (r) =>
        r.registrationStatus?.toLowerCase() === "upcoming" ||
        r.registrationStatus?.toLowerCase() === "registered"
    ).length;

    completedCount.value = trainings.filter(
      (r) => r.registrationStatus?.toLowerCase() === "completed"
    ).length;
  } catch (error) {
    console.error("❌ Error fetching training counters:", error);
  }
}

const handleUpdate = async () => {
  if (!form.value.currentPassword) {
    alert("You must enter your current password to save changes.");
    return;
  }

  try {
    await axios.put("http://127.0.0.1:8000/api/user", form.value, {
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
    });
    alert("Profile updated successfully!");
    router.push("/homepage");
  } catch (error) {
    alert(error.response?.data?.message || "Update failed.");
  }
};

const handleDelete = async () => {
  try {
    isDeleting.value = true;
    const res = await axios.delete("http://127.0.0.1:8000/api/user", {
      headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      data: { currentPassword: form.value.currentPassword }, // send password for verification
    });
    alert(res.data.message);
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    router.push("/auth/login");
  } catch (error) {
    alert(error.response?.data?.message || "Deletion failed.");
  } finally {
    isDeleting.value = false;
    showDeleteModal.value = false;
  }
};

const logout = () => {
  // Remove user data from localStorage
  localStorage.removeItem("user");
  // Redirect to login page
  router.push({ name: "Login" });
};
</script>

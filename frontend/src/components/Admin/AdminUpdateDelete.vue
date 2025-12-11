<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <!--Large screen-->
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Right Column -->
      <div class="w-full lg:mt-0 lg:ml-0 flex flex-col gap-6">
        <!-- Bottom Row: Event List -->
        <div class="bg-white rounded-lg shadow p-6 flex-1">
          <header
            class="sticky top-0 z-10 bg-white h-16 text-black flex items-center px-4"
          >
            <h1 class="text-3xl font-bold">Account Setting</h1>
          </header>

          <!-- FORM -->
          <form @submit.prevent="handleUpdate" class="space-y-4 pt-4">
            <div class="divider sm:hidden"></div>

            <!-- Email (Disabled) -->
            <div>
              <input
                class="border border-gray-300 input w-full text-gray-600 bg-gray-100"
                type="email"
                placeholder="Email"
                v-model="form.emailAddress"
                disabled
              />
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <!-- New & Confirm Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="relative">
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  class="border border-gray-300 input w-full pr-10"
                  placeholder="New Password"
                  v-model="form.newPassword"
                />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500"
                  aria-label="Toggle password visibility"
                  @click="showNewPassword = !showNewPassword"
                >
                  <span v-if="showNewPassword">
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
              <div class="w-full">
                <div class="relative">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="border input w-full pr-10"
                    :class="passwordMatchClass"
                    placeholder="Confirm New Password"
                    v-model="form.confirmPassword"
                  />
                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500"
                  aria-label="Toggle confirm password visibility"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <span v-if="showConfirmPassword">
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
                <p
                  v-if="showPasswordMismatch"
                  class="text-red-500 text-sm mt-1"
                >
                  Passwords do not match.
                </p>
              </div>
            </div>

            <!-- Divider for large screens -->
            <div class="hidden lg:block h-px bg-gray-300"></div>

            <div class="relative">
              <input
                :type="showCurrentPassword ? 'text' : 'password'"
                class="border border-gray-300 input w-full pr-10"
                placeholder="Current Password"
                v-model="form.currentPassword"
                required
              />
              <button
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500"
                aria-label="Toggle password visibility"
                @click="showCurrentPassword = !showCurrentPassword"
              >
                <span v-if="showCurrentPassword">
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
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { authState } from "@/stores/authState";
const router = useRouter();
const showDeleteModal = ref(false);
const isDeleting = ref(false);
const upcomingCount = ref(0);
const completedCount = ref(0);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const showCurrentPassword = ref(false);

// Computed property to check if passwords match
const passwordsMatch = computed(() => {
  if (!form.value.newPassword && !form.value.confirmPassword) return true; // no input yet
  return form.value.newPassword === form.value.confirmPassword;
});

// Dynamic class for confirm password input
const passwordMatchClass = computed(() =>
  passwordsMatch.value ? "border-gray-300" : "border-red-500"
);

// Show mismatch message only if user typed something
const showPasswordMismatch = computed(
  () => form.value.confirmPassword && !passwordsMatch.value
);

const form = ref({
  emailAddress: "",
  newPassword: "",
  confirmPassword: "",
  currentPassword: "",
});

const userName = ref("");

onMounted(async () => {
  try {
    // Use token from authState or localStorage
    const token = authState.token || localStorage.getItem("admin_token");
    if (!token) {
      console.error("❌ No admin token found, redirecting to login.");
      router.push("/admin/login");
      return;
    }

    const res = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/admin/details",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    const admin = res.data;

    // Update reactive form and store
    form.value.emailAddress = admin.emailAddress || "";

    authState.user = admin;
    localStorage.setItem("user", JSON.stringify(admin));

  } catch (error) {
    console.error("❌ Error fetching admin details:", error.response?.data?.message || error);
  }
});

function deleteAccount() {
  axios
    .delete(import.meta.env.VITE_API_BASE_URL + "/user", {
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

  // Validate new password if provided
  if (form.value.newPassword && form.value.newPassword !== form.value.confirmPassword) {
    alert("New password and confirm password do not match.");
    return;
  }

  try {
    const token = authState.token || localStorage.getItem("admin_token");
    await axios.put(import.meta.env.VITE_API_BASE_URL + "/admin/update", {
      currentPassword: form.value.currentPassword,
      newPassword: form.value.newPassword || null,
      confirmPassword: form.value.confirmPassword || null,
    }, {
      headers: { Authorization: `Bearer ${token}` },
    });
    alert("Password updated successfully!");
    // Clear password fields
    form.value.newPassword = "";
    form.value.confirmPassword = "";
    form.value.currentPassword = "";
  } catch (error) {
    alert(error.response?.data?.message || error.response?.data?.errors?.currentPassword?.[0] || "Update failed.");
  }
};

const handleDelete = async () => {
  try {
    isDeleting.value = true;
    const res = await axios.delete(
      import.meta.env.VITE_API_BASE_URL + "/user",
      {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
        data: { currentPassword: form.value.currentPassword }, // send password for verification
      }
    );
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

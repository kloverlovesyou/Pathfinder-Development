<script setup>
import { useRouter } from "vue-router";

import { reactive, ref, onMounted } from "vue";

import html2pdf from "html2pdf.js";
import { computed } from "vue";

const filteredExperiences = computed(() =>
  resume.experiences.filter((exp) => exp.text)
);

const router = useRouter();
const isModalOpen = ref(false);
const selectedImage = ref(null);
const selectedTitle = ref(null);
const newSkill = ref("");
const skills = ref([]);
const resumePreview = ref(null);
const experiences = reactive([{ text: "" }]);
const showModal = ref(false);

// Resume data
const resume = ref({
  firstName: "",
  middleName: "",
  lastName: "",
  email: "",
  mobile: "",
  address: "",
  summary: "",
  experiences: [],
  education: [],
  skills: [],        // ✅ single source of truth
  certificates: [],
  url: "",
});

function openPreview() {
  showModal.value = true;
}

function downloadPDF() {
  const element = resumePreview.value;
  const opt = {
    margin: 0.5,
    filename: "resume.pdf",
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: "in", format: "letter", orientation: "portrait" },
  };
  html2pdf().set(opt).from(element).save();
}

function addExperience() {
  resume.experiences.push({ text: "" });
}

function removeExperience(index) {
  resume.experiences.splice(index, 1);
}


// Add skill
function addSkill() {
  if (newSkill.value.trim() !== "") {
    resume.value.skills.push(newSkill.value.trim()); // ✅ push into resume.skills
    newSkill.value = ""; // clear input after adding
  }
}

// Remove skill
function removeSkill(index) {
  resume.value.skills.splice(index, 1); // ✅ remove from resume.skills
}
function openModal(image, title) {
  selectedImage.value = image;
  selectedTitle.value = title;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

const certificates = reactive([
  {
    title: "",
    image: null,
  },
]);

function addCertificate() {
  certificates.push({ title: "", image: null });
}

function removeCertificate(index) {
  certificates.splice(index, 1);
}

function handleFileUpload(event, index) {
  const file = event.target.files[0];
  if (file) {
    certificates[index].image = URL.createObjectURL(file);
  }
}

const educationList = reactive([{ text: "" }]);

function addEducation() {
  resume.value.education.push({
    attainment: "",
    university: "",
    year: "",
  });
}

function removeEducation(index) {
  resume.value.education.splice(index, 1);
}

// 👇 NEW: Form data for autofill
const form = reactive({
  firstName: "",
  middleName: "",
  lastName: "",
  emailAddress: "",
  phoneNumber: "",
  address: "",
});

const userName = ref(""); // <-- define it

onMounted(() => {
  const savedUser = localStorage.getItem("user");
  if (savedUser) {
    const user = JSON.parse(savedUser);

    // Fill form
    form.firstName = user.firstName || "";
    form.middleName = user.middleName || "";
    form.lastName = user.lastName || "";
    form.emailAddress = user.emailAddress || "";
    form.phoneNumber = user.phoneNumber || "";
    form.address = user.address || "";

    // Fill resume
    resume.value.firstName = user.firstName || "";
    resume.value.middleName = user.middleName || "";
    resume.value.lastName = user.lastName || "";
    resume.value.email = user.emailAddress || "";
    resume.value.mobile = user.phoneNumber || "";
    resume.value.address = user.address || "";

    // set userName (e.g., "First Last")
    userName.value = `${user.firstName || ""} ${user.lastName || ""}`.trim();
  }
});
</script>

<template>
  <div class="min-h-screen m-3 p-4 rounded-lg font-poppins">
    <!--Large screen-->
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Left Column -->
      <div
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-4 pt-8 flex flex-col items-center hidden lg:flex"
      >
        <!-- Avatar -->
        <div class="w-24 h-24 rounded-full bg-white mb-4">
          <div class="avatar w-24 h-24 rounded-full bg-white mb-4">
            <img
              src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
              alt="User Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
        </div>

        <!-- Name -->
        <h2 class="text-xl font-semibold mb-6">{{ userName }}</h2>
        <div
          class="w-full flex items-center justify-center gap-6 mb-6 relative"
        >
          <!-- Upcoming -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Upcoming</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              0
            </span>
          </div>

          <!-- Completed -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Completed</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              0
            </span>
          </div>
        </div>

        <!-- Buttons -->
        <div class="w-full flex flex-col gap-3">
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'ResumeEditorpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 34 34"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Resume</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Certificatespage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 35 35"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Certificates</span>
          </button>

          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Bookmarkpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 31 30"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 3.75V30L17.4375 20.625L27.125 30V3.75H7.75ZM23.25 0H3.875V26.25L5.8125 24.375V1.875H23.25V0Z"
                fill="white"
              />
            </svg>

            <span>Bookmark</span>
          </button>
          <div class="divider"></div>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'UpdateDeletepage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
                fill="white"
              />
            </svg>

            <span>Update/Delete Account</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Login' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.24 22.27H15.11C10.67 22.27 8.53002 20.52 8.16002 16.6C8.12002 16.19 8.42002 15.82 8.84002 15.78C9.24002 15.74 9.62002 16.05 9.66002 16.46C9.95002 19.6 11.43 20.77 15.12 20.77H15.25C19.32 20.77 20.76 19.33 20.76 15.26V8.73998C20.76 4.66998 19.32 3.22998 15.25 3.22998H15.12C11.41 3.22998 9.93002 4.41998 9.66002 7.61998C9.61002 8.02998 9.26002 8.33998 8.84002 8.29998C8.42002 8.26998 8.12001 7.89998 8.15001 7.48998C8.49001 3.50998 10.64 1.72998 15.11 1.72998H15.24C20.15 1.72998 22.25 3.82998 22.25 8.73998V15.26C22.25 20.17 20.15 22.27 15.24 22.27Z"
                fill="white"
              />
              <path
                d="M15.0001 12.75H3.62012C3.21012 12.75 2.87012 12.41 2.87012 12C2.87012 11.59 3.21012 11.25 3.62012 11.25H15.0001C15.4101 11.25 15.7501 11.59 15.7501 12C15.7501 12.41 15.4101 12.75 15.0001 12.75Z"
                fill="white"
              />
              <path
                d="M5.84994 16.1C5.65994 16.1 5.46994 16.03 5.31994 15.88L1.96994 12.53C1.67994 12.24 1.67994 11.76 1.96994 11.47L5.31994 8.12003C5.60994 7.83003 6.08994 7.83003 6.37994 8.12003C6.66994 8.41003 6.66994 8.89003 6.37994 9.18003L3.55994 12L6.37994 14.82C6.66994 15.11 6.66994 15.59 6.37994 15.88C6.23994 16.03 6.03994 16.1 5.84994 16.1Z"
                fill="white"
              />
            </svg>

            <span>Logout</span>
          </button>
        </div>
      </div>

      <!-- Right Column -->
      <div class="w-full lg:w-3/4 lg:pl-6 mt-6 lg:mt-0 flex flex-col gap-6">
        <div class="p-6 bg-white shadow rounded-lg">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Resume Editor</h1>
            <button @click="isModalOpen = true" class="group px-2 py-2">
              <svg
                class="block group-hover:hidden"
                width="34"
                height="34"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M20.5 10.19H17.61C15.24 10.19 13.31 8.26 13.31 5.89V3C13.31 2.45 12.86 2 12.31 2H8.07C4.99 2 2.5 4 2.5 7.57V16.43C2.5 20 4.99 22 8.07 22H15.93C19.01 22 21.5 20 21.5 16.43V11.19C21.5 10.64 21.05 10.19 20.5 10.19ZM12.28 15.78L10.28 17.78C10.21 17.85 10.12 17.91 10.03 17.94C9.94 17.98 9.85 18 9.75 18C9.65 18 9.56 17.98 9.47 17.94C9.39 17.91 9.31 17.85 9.25 17.79C9.24 17.78 9.23 17.78 9.23 17.77L7.23 15.77C6.94 15.48 6.94 15 7.23 14.71C7.52 14.42 8 14.42 8.29 14.71L9 15.44V11.25C9 10.84 9.34 10.5 9.75 10.5C10.16 10.5 10.5 10.84 10.5 11.25V15.44L11.22 14.72C11.51 14.43 11.99 14.43 12.28 14.72C12.57 15.01 12.57 15.49 12.28 15.78Z"
                  fill="#6682A3"
                />
                <path
                  d="M17.4299 8.80999C18.3799 8.81999 19.6999 8.81999 20.8299 8.81999C21.3999 8.81999 21.6999 8.14999 21.2999 7.74999C19.8599 6.29999 17.2799 3.68999 15.7999 2.20999C15.3899 1.79999 14.6799 2.07999 14.6799 2.64999V6.13999C14.6799 7.59999 15.9199 8.80999 17.4299 8.80999Z"
                  fill="#6682A3"
                />
              </svg>
              <svg
                class="hidden group-hover:block"
                width="34"
                height="34"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M20.5 10.19H17.61C15.24 10.19 13.31 8.26 13.31 5.89V3C13.31 2.45 12.86 2 12.31 2H8.07C4.99 2 2.5 4 2.5 7.57V16.43C2.5 20 4.99 22 8.07 22H15.93C19.01 22 21.5 20 21.5 16.43V11.19C21.5 10.64 21.05 10.19 20.5 10.19ZM12.28 15.78L10.28 17.78C10.21 17.85 10.12 17.91 10.03 17.94C9.94 17.98 9.85 18 9.75 18C9.65 18 9.56 17.98 9.47 17.94C9.39 17.91 9.31 17.85 9.25 17.79C9.24 17.78 9.23 17.78 9.23 17.77L7.23 15.77C6.94 15.48 6.94 15 7.23 14.71C7.52 14.42 8 14.42 8.29 14.71L9 15.44V11.25C9 10.84 9.34 10.5 9.75 10.5C10.16 10.5 10.5 10.84 10.5 11.25V15.44L11.22 14.72C11.51 14.43 11.99 14.43 12.28 14.72C12.57 15.01 12.57 15.49 12.28 15.78Z"
                  fill="#44576D"
                />
                <path
                  d="M17.4299 8.80999C18.3799 8.81999 19.6999 8.81999 20.8299 8.81999C21.3999 8.81999 21.6999 8.14999 21.2999 7.74999C19.8599 6.29999 17.2799 3.68999 15.7999 2.20999C15.3899 1.79999 14.6799 2.07999 14.6799 2.64999V6.13999C14.6799 7.59999 15.9199 8.80999 17.4299 8.80999Z"
                  fill="#44576D"
                />
              </svg>
            </button>
          </div>

          <form class="space-y-6">
            <!-- Personal Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input
                v-model="form.firstName"
                type="text"
                placeholder="First Name"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.emailAddress"
                type="email"
                placeholder="Email"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.middleName"
                type="text"
                placeholder="Middle Name"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.phoneNumber"
                type="text"
                placeholder="Mobile Number"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.lastName"
                type="text"
                placeholder="Last Name"
                class="input-field border rounded p-2"
              />

              <input
                v-model="form.address"
                type="text"
                placeholder="Address"
                class="input-field border rounded p-2"
              />
            </div>

            <!-- Professional Summary -->
            <div class="border rounded p-4 space-y-4 relative">
              <label class="block text-lg font-semibold mb-1"
                >Professional Summary</label
              >
              <div class="relative border rounded p-3 rounded-lg">
                <textarea
                  rows="3"
                  class="input-field w-full"
                  v-model="resume.summary"
                ></textarea>
              </div>
            </div>

            <!-- Professional Experience -->
            <div class="border rounded p-4 space-y-4 relative">
              <!-- Header with Plus Button -->
              <div class="flex justify-between items-center">
                <label class="text-lg font-semibold"
                  >Professional Experience</label
                >
                <button
                  type="button"
                  @click="addExperience"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Experience Items -->
              <div
                v-for="(exp, index) in resume.experiences"
                :key="index"
                class="relative border p-3 rounded"
              >
                <!-- Delete Button -->
                <button
                  type="button"
                  @click="removeExperience(index)"
                  class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
                >
                  ✕
                </button>

                <!-- Experience Field -->
                <textarea
                  v-model="resume.experiences[index].text"
                  rows="3"
                  placeholder="Describe your professional experience..."
                  class="input-field w-full"
                ></textarea>
              </div>
            </div>

            <div class="border rounded p-4 space-y-4 relative">
              <!-- Header with Plus Button -->
              <div class="flex justify-between items-center">
                <label class="text-lg font-semibold">Education</label>
                <button
                  type="button"
                  @click="addEducation"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Education Items -->
              <div
                v-for="(edu, index) in resume.education"
                :key="index"
                class="relative border p-3 rounded space-y-3"
              >
                <!-- Delete Button -->
                <button
                  type="button"
                  @click="removeEducation(index)"
                  class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
                >
                  ✕
                </button>

                <!-- Educational Attainment -->
                <div>
                  <label class="block font-medium mb-1"
                    >Educational Attainment</label
                  >
                  <select
                    v-model="edu.attainment"
                    class="input-field border rounded w-full p-2"
                  >
                    <option value="" disabled>Select level</option>
                    <option>High School</option>
                    <option>Bachelor's Degree</option>
                    <option>Master's Degree</option>
                    <option>Doctorate</option>
                    <option>Others</option>
                  </select>
                </div>

                <!-- University Name -->
                <div>
                  <label class="block font-medium mb-1">University</label>
                  <input
                    v-model="edu.university"
                    type="text"
                    placeholder="Enter university name"
                    class="input-field border rounded w-full p-2"
                  />
                </div>

                <!-- Year -->
                <div>
                  <label class="block font-medium mb-1">Year</label>
                  <input
                    v-model="edu.year"
                    type="text"
                    placeholder="e.g. 2020"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
              </div>
            </div>

              <!-- Skills -->
              <div class="border rounded p-4 space-y-3">
                <label class="text-lg font-semibold">Skills</label>
                <div class="flex gap-2">
                  <!-- 👇 use newSkill, not resume.skills -->
                  <input
                    v-model="newSkill"
                    type="text"
                    placeholder="Type a skill"
                    class="input-field flex-1 border"
                  />
                  <button
                    type="button"
                    @click="addSkill"
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                  >
                    +
                  </button>
                </div>

              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="(skill, index) in resume.skills"
                  :key="index"
                  class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full flex items-center gap-2"
                >
                  {{ skill }}
                  <button
                    type="button"
                    @click="removeSkill(index)"
                    class="text-blue-500 hover:text-red-500"
                  >
                    ✕
                  </button>
                </span>
              </div>
            </div>

            <!-- URL -->
            <div class="border rounded p-4 space-y-4 relative">
              <h2 class="text-lg font-semibold">URL</h2>
              <input
                type="url"
                placeholder="URL"
                class="input-field border rounded p-2 w-full"
                v-model="resume.url"
              />
            </div>

            <!-- Save Button -->
            <button
              type="button"
              class="w-full py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
            >
              Save Resume
            </button>
          </form>
          <!-- Modal -->
          <div
            v-if="isModalOpen"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
          >
            <div
              class="bg-white w-[800px] max-h-[90vh] rounded-xl shadow-lg overflow-y-auto relative"
            >
              <!-- Close button -->
              <button
                @click="isModalOpen = false"
                class="absolute top-3 right-3 text-gray-600 hover:text-red-600 text-xl"
              >
                ✕
              </button>

              <!-- Resume Preview -->
              <div
                ref="resumePreview"
                style="
                  padding: 1.5rem;
                  color: #111111;
                  background-color: #ffffff;
                "
              >
                <!-- Name -->
                <h1
                  style="
                    font-size: 1.875rem;
                    font-weight: bold;

                    margin-bottom: 0.5rem;
                  "
                >
                  {{ resume.firstName }} {{ resume.middleName }}
                  {{ resume.lastName }}
                </h1>

                <!-- Contact -->
                <p style="color: #4b5563; margin: 0">
                  {{ resume.email }} | {{ resume.mobile }} |
                  {{ resume.address }} <br />
                  <span v-if="resume.url">{{ resume.url }}</span>
                </p>

                <!-- Summary -->
                <section v-if="resume.summary" style="margin-top: 1.5rem">
                  <h2
                    style="
                      font-size: 1.25rem;
                      font-weight: 600;
                      color: #000000;
                      border-bottom: 1px solid #000000;
                      padding-bottom: 0.25rem;
                    "
                  >
                    Professional Summary
                  </h2>
                  <p
                    style="
                      margin-top: 0.5rem;
                      color: #374151;
                      white-space: pre-line;
                    "
                  >
                    {{ resume.summary }}
                  </p>
                </section>

                <!-- Experience -->
                <section
                  v-if="resume.experiences.length"
                  style="margin-top: 1.5rem"
                >
                  <h2
                    style="
                      font-size: 1.25rem;
                      font-weight: 600;
                      color: #000000;
                      border-bottom: 1px solid #000000;
                      padding-bottom: 0.25rem;
                    "
                  >
                    Professional Experience
                  </h2>
                  <ul
                    style="
                      list-style-type: disc;
                      margin-left: 1.5rem;
                      margin-top: 0.5rem;
                    "
                  >
                    <li
                      v-for="(exp, i) in resume.experiences"
                      :key="i"
                      style="margin-bottom: 0.25rem; color: #000000"
                    >
                      {{ exp.text }}
                    </li>
                  </ul>
                </section>

                <!-- Education -->
                <section
                  v-if="resume.education.length"
                  style="margin-top: 1.5rem"
                >
                  <h2
                    style="
                      font-size: 1.25rem;
                      font-weight: 600;
                      color: #000000;
                      border-bottom: 1px solid #000000;
                      padding-bottom: 0.25rem;
                    "
                  >
                    Education
                  </h2>
                  <div
                    v-for="(edu, i) in resume.education"
                    :key="i"
                    style="margin-top: 0.5rem"
                  >
                    <p
                      v-if="edu.university"
                      style="font-weight: 600; color: #000000; margin: 0"
                    >
                      {{ edu.university }}
                    </p>
                    <p style="color: #4b5563; margin: 0">
                      {{ edu.attainment
                      }}<span v-if="edu.year"> ({{ edu.year }})</span>
                    </p>
                  </div>
                </section>

                <!-- Skills -->
                <section v-if="resume.skills.length" style="margin-top: 1.5rem">
                  <h2
                    style="
                      font-size: 1.25rem;
                      font-weight: 600;
                      color: #000000;
                      border-bottom: 1px solid #000000;
                      padding-bottom: 0.25rem;
                    "
                  >
                    Skills
                  </h2>
                  <div style="margin-top: 0.5rem">
                    <span
                      v-for="(skill, i) in resume.skills"
                      :key="i"
                      style="
                        display: inline-block;
                        margin-right: 0.5rem;
                        margin-bottom: 0.5rem;
                        padding: 0.25rem 0.75rem;
                        border-radius: 9999px;
                        font-size: 0.875rem;
                        background-color: #dbeafe;
                        color: #1e40af;
                      "
                    >
                      {{ skill }}
                    </span>
                  </div>
                </section>
              </div>

              <!-- Confirm Download -->
              <div
                style="
                  padding: 1rem;
                  border-top: 1px solid #e5e7eb;
                  margin-top: 1rem;
                  display: flex;
                  justify-content: flex-end;
                "
              >
                <button
                  @click="downloadPDF"
                  style="
                    padding: 0.5rem 1.5rem;
                    border-radius: 0.5rem;
                    font-weight: 600;
                    color: #ffffff;
                    background-color: #2563eb;
                    border: none;
                    cursor: pointer;
                  "
                >
                  Confirm & Download PDF
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
/* Force html2canvas-compatible colors */
.bg-blue-600 {
  background-color: #2563eb !important;
}
.bg-blue-700 {
  background-color: #1d4ed8 !important;
}
.text-gray-600 {
  color: #4b5563 !important;
}
.text-red-600 {
  color: #dc2626 !important;
}
.text-white {
  color: #ffffff !important;
}
.bg-white {
  background-color: #ffffff !important;
}
</style>

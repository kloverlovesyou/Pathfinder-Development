<script setup>
import { useRouter } from "vue-router";
import { reactive } from "vue";
import { ref } from "vue";

const router = useRouter();
const isModalOpen = ref(false);
const selectedImage = ref(null);
const selectedTitle = ref(null);
const newSkill = ref("");
const skills = ref([]);

const experiences = reactive([{ text: "" }]);

function addExperience() {
  experiences.push({ text: "" });
}

function removeExperience(index) {
  experiences.splice(index, 1);
}

function addSkill() {
  if (newSkill.value.trim() !== "") {
    skills.value.push(newSkill.value.trim());
    newSkill.value = "";
  }
}

function removeSkill(index) {
  skills.value.splice(index, 1);
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
  educationList.push({ text: "" });
}

function removeEducation(index) {
  educationList.splice(index, 1);
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 font-poppins">
    <div class="absolute right-5 dropdown dropdown-end">
      <div tabindex="0" role="button" class="group m-5">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="size-5 flex-shrink-0 group-hover:hidden"
          viewBox="0 0 24 24"
          fill="white"
        >
          <path
            d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
          />
        </svg>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="size-5 flex-shrink-0 group-hover:block hidden"
          viewBox="0 0 24 24"
          fill="dark-slate"
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
        <li>Update/Delete Account</li>
        <li><a>Logout</a></li>
      </ul>
    </div>
    <div class="lg:hidden block font-poppins">
      <!-- Row 1: Profile Info -->
      <div class="bg-dark-slate text-white p-6 flex flex-col items-center">
        <!-- Avatar -->
        <div class="avatar w-24 h-24 rounded-full bg-white mb-4">
          <img
            src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
            alt="User Avatar"
            class="w-full h-full object-cover rounded-full"
          />
        </div>

        <!-- Name -->
        <h2 class="text-xl font-semibold mb-2">Keiro Musician</h2>

        <!-- Stats -->
        <div class="flex gap-8 mb-4">
          <div class="text-center">
            <p class="text-2xl font-bold">0</p>
            <p class="text-sm">Upcoming</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold">0</p>
            <p class="text-sm">Completed</p>
          </div>
        </div>
      </div>

      <!-- Row 2: Buttons -->
      <div class="bg-customButton text-white p-4 flex justify-center gap-4">
        <button
          class="group flex flex-col items-center justify-center px-4 py-3 rounded text-white hover:text-dark-slate"
          @click="router.push('/calendarpage')"
        >
          <svg
            class="size-6 flex-shrink-0 group-hover:hidden"
            viewBox="0 0 33 34"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M2.75 11.0697C2.75 9.18408 2.75 8.24128 3.33579 7.65549C3.92157 7.0697 4.86438 7.0697 6.75 7.0697H26.25C28.1356 7.0697 29.0784 7.0697 29.6642 7.65549C30.25 8.24128 30.25 9.18408 30.25 11.0697V13.1394C30.25 13.6108 30.25 13.8465 30.1036 13.9929C29.9571 14.1394 29.7214 14.1394 29.25 14.1394H3.75C3.2786 14.1394 3.04289 14.1394 2.89645 13.9929C2.75 13.8465 2.75 13.6108 2.75 13.1394V11.0697Z"
              fill="white"
            />
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M2.75 27.1065C2.75 28.9921 2.75 29.9349 3.33579 30.5207C3.92157 31.1065 4.86438 31.1065 6.75 31.1065H26.25C28.1356 31.1065 29.0784 31.1065 29.6642 30.5207C30.25 29.9349 30.25 28.9921 30.25 27.1065V17.9672C30.25 17.4958 30.25 17.2601 30.1036 17.1136C29.9571 16.9672 29.7214 16.9672 29.25 16.9672H3.75C3.2786 16.9672 3.04289 16.9672 2.89645 17.1136C2.75 17.2601 2.75 17.4958 2.75 17.9672V27.1065ZM9.625 20.795C9.625 20.3236 9.625 20.0879 9.77145 19.9415C9.91789 19.795 10.1536 19.795 10.625 19.795H14.125C14.5964 19.795 14.8321 19.795 14.9786 19.9415C15.125 20.0879 15.125 20.3236 15.125 20.795V21.6229C15.125 22.0943 15.125 22.33 14.9786 22.4765C14.8321 22.6229 14.5964 22.6229 14.125 22.6229H10.625C10.1536 22.6229 9.91789 22.6229 9.77145 22.4765C9.625 22.33 9.625 22.0943 9.625 21.6229V20.795ZM9.77145 25.5972C9.625 25.7437 9.625 25.9794 9.625 26.4508V27.2786C9.625 27.75 9.625 27.9857 9.77145 28.1322C9.91789 28.2786 10.1536 28.2786 10.625 28.2786H14.125C14.5964 28.2786 14.8321 28.2786 14.9786 28.1322C15.125 27.9857 15.125 27.75 15.125 27.2786V26.4508C15.125 25.9794 15.125 25.7437 14.9786 25.5972C14.8321 25.4508 14.5964 25.4508 14.125 25.4508H10.625C10.1536 25.4508 9.91789 25.4508 9.77145 25.5972ZM17.875 20.795C17.875 20.3236 17.875 20.0879 18.0214 19.9415C18.1679 19.795 18.4036 19.795 18.875 19.795H22.375C22.8464 19.795 23.0821 19.795 23.2286 19.9415C23.375 20.0879 23.375 20.3236 23.375 20.795V21.6229C23.375 22.0943 23.375 22.33 23.2286 22.4765C23.0821 22.6229 22.8464 22.6229 22.375 22.6229H18.875C18.4036 22.6229 18.1679 22.6229 18.0214 22.4765C17.875 22.33 17.875 22.0943 17.875 21.6229V20.795ZM18.0214 25.5972C17.875 25.7437 17.875 25.9794 17.875 26.4508V27.2786C17.875 27.75 17.875 27.9857 18.0214 28.1322C18.1679 28.2786 18.4036 28.2786 18.875 28.2786H22.375C22.8464 28.2786 23.0821 28.2786 23.2286 28.1322C23.375 27.9857 23.375 27.75 23.375 27.2786V26.4508C23.375 25.9794 23.375 25.7437 23.2286 25.5972C23.0821 25.4508 22.8464 25.4508 22.375 25.4508H18.875C18.4036 25.4508 18.1679 25.4508 18.0214 25.5972Z"
              fill="white"
            />
            <path
              d="M9.625 4.24182L9.625 8.48362"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
            <path
              d="M23.375 4.24182L23.375 8.48362"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>
          <svg
            class="size-6 flex-shrink-0 hidden group-hover:block"
            viewBox="0 0 33 34"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M2.75 11.0697C2.75 9.18408 2.75 8.24128 3.33579 7.65549C3.92157 7.0697 4.86438 7.0697 6.75 7.0697H26.25C28.1356 7.0697 29.0784 7.0697 29.6642 7.65549C30.25 8.24128 30.25 9.18408 30.25 11.0697V13.1394C30.25 13.6108 30.25 13.8465 30.1036 13.9929C29.9571 14.1394 29.7214 14.1394 29.25 14.1394H3.75C3.2786 14.1394 3.04289 14.1394 2.89645 13.9929C2.75 13.8465 2.75 13.6108 2.75 13.1394V11.0697Z"
              fill="#44576D"
            />
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M2.75 27.1065C2.75 28.9921 2.75 29.9349 3.33579 30.5207C3.92157 31.1065 4.86438 31.1065 6.75 31.1065H26.25C28.1356 31.1065 29.0784 31.1065 29.6642 30.5207C30.25 29.9349 30.25 28.9921 30.25 27.1065V17.9672C30.25 17.4958 30.25 17.2601 30.1036 17.1136C29.9571 16.9672 29.7214 16.9672 29.25 16.9672H3.75C3.2786 16.9672 3.04289 16.9672 2.89645 17.1136C2.75 17.2601 2.75 17.4958 2.75 17.9672V27.1065ZM9.625 20.795C9.625 20.3236 9.625 20.0879 9.77145 19.9415C9.91789 19.795 10.1536 19.795 10.625 19.795H14.125C14.5964 19.795 14.8321 19.795 14.9786 19.9415C15.125 20.0879 15.125 20.3236 15.125 20.795V21.6229C15.125 22.0943 15.125 22.33 14.9786 22.4765C14.8321 22.6229 14.5964 22.6229 14.125 22.6229H10.625C10.1536 22.6229 9.91789 22.6229 9.77145 22.4765C9.625 22.33 9.625 22.0943 9.625 21.6229V20.795ZM9.77145 25.5972C9.625 25.7437 9.625 25.9794 9.625 26.4508V27.2786C9.625 27.75 9.625 27.9857 9.77145 28.1322C9.91789 28.2786 10.1536 28.2786 10.625 28.2786H14.125C14.5964 28.2786 14.8321 28.2786 14.9786 28.1322C15.125 27.9857 15.125 27.75 15.125 27.2786V26.4508C15.125 25.9794 15.125 25.7437 14.9786 25.5972C14.8321 25.4508 14.5964 25.4508 14.125 25.4508H10.625C10.1536 25.4508 9.91789 25.4508 9.77145 25.5972ZM17.875 20.795C17.875 20.3236 17.875 20.0879 18.0214 19.9415C18.1679 19.795 18.4036 19.795 18.875 19.795H22.375C22.8464 19.795 23.0821 19.795 23.2286 19.9415C23.375 20.0879 23.375 20.3236 23.375 20.795V21.6229C23.375 22.0943 23.375 22.33 23.2286 22.4765C23.0821 22.6229 22.8464 22.6229 22.375 22.6229H18.875C18.4036 22.6229 18.1679 22.6229 18.0214 22.4765C17.875 22.33 17.875 22.0943 17.875 21.6229V20.795ZM18.0214 25.5972C17.875 25.7437 17.875 25.9794 17.875 26.4508V27.2786C17.875 27.75 17.875 27.9857 18.0214 28.1322C18.1679 28.2786 18.4036 28.2786 18.875 28.2786H22.375C22.8464 28.2786 23.0821 28.2786 23.2286 28.1322C23.375 27.9857 23.375 27.75 23.375 27.2786V26.4508C23.375 25.9794 23.375 25.7437 23.2286 25.5972C23.0821 25.4508 22.8464 25.4508 22.375 25.4508H18.875C18.4036 25.4508 18.1679 25.4508 18.0214 25.5972Z"
              fill="#44576D"
            />
            <path
              d="M9.625 4.24182L9.625 8.48362"
              stroke="#44576D"
              stroke-width="2"
              stroke-linecap="round"
            />
            <path
              d="M23.375 4.24182L23.375 8.48362"
              stroke="#44576D"
              stroke-width="2"
              stroke-linecap="round"
            />
          </svg>

          <span>Calendar</span>
        </button>
        <button
          class="group flex flex-col items-center justify-center px-4 py-3 rounded text-white hover:text-dark-slate"
          @click="router.push('/bookmarkpage')"
        >
          <svg
            class="size-6 flex-shrink-0 group-hover:hidden"
            viewBox="0 0 18 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M3 3V24L10.5 16.5L18 24V3H3ZM15 0H0V21L1.5 19.5V1.5H15V0Z"
              fill="white"
            />
          </svg>
          <svg
            class="size-6 flex-shrink-0 group-hover:block hidden"
            viewBox="0 0 31 30"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M7.75 3.75V30L17.4375 20.625L27.125 30V3.75H7.75ZM23.25 0H3.875V26.25L5.8125 24.375V1.875H23.25V0Z"
              fill="#44576D"
            />
          </svg>

          <span>Bookmark</span>
        </button>
        <button
          class="group flex flex-col items-center justify-center px-4 py-3 rounded text-white hover:text-dark-slate"
          @click="router.push('/certificatespage')"
        >
          <svg
            class="size-6 flex-shrink-0 group-hover:hidden"
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
          <svg
            class="size-6 flex-shrink-0 hidden group-hover:block"
            viewBox="0 0 35 35"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
              stroke="#44576D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>

          <span>Certificates</span>
        </button>
        <button
          class="group flex flex-col items-center justify-center px-4 py-3 rounded text-white hover:text-dark-slate"
        >
          <svg
            class="size-6 flex-shrink-0 group-hover:hidden"
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
          <svg
            class="size-6 flex-shrink-0 hidden group-hover:block"
            viewBox="0 0 34 34"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
              stroke="#44576D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>

          <span>Resume</span>
        </button>
      </div>

      <!-- Row 3: Editor -->
      <div class="bg-white p-6">
        <div class="p-6 bg-white shadow-lg rounded-2xl">
          <div class="absolute right-4 dropdown dropdown-end">
            <div tabindex="0" role="button" class="group">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5 flex-shrink-0 group-hover:hidden"
                viewBox="0 0 24 24"
                fill="dark-slate"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5 flex-shrink-0 group-hover:block hidden"
                viewBox="0 0 24 24"
                fill="gray"
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
              <li>Download Resume</li>
            </ul>
          </div>
          <h1 class="text-2xl font-bold mb-6">Resume Editor</h1>

          <form class="space-y-6">
            <!-- Personal Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input
                type="text"
                placeholder="First Name"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Middle Name"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Last Name"
                class="input-field border p-2"
              />
              <input
                type="email"
                placeholder="Email"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Mobile Number"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Address"
                class="input-field border p-2"
              />
            </div>

            <!-- Professional Summary -->
            <div class="border rounded-xl p-4 space-y-3">
              <label class="block text-lg font-semibold mb-1"
                >Professional Summary</label
              >
              <textarea rows="3" class="border input-field w-full"></textarea>
            </div>

            <!-- Professional Experience -->
            <div class="border rounded-xl p-4 space-y-4 relative">
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
                v-for="(exp, index) in experiences"
                :key="index"
                class="relative border p-3 rounded-lg"
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
                  v-model="exp.text"
                  rows="3"
                  placeholder="Describe your professional experience..."
                  class="input-field w-full"
                ></textarea>
              </div>
            </div>

            <!-- Education -->
            <div class="border rounded-xl p-4 space-y-4 relative">
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
                v-for="(edu, index) in educationList"
                :key="index"
                class="relative border p-3 rounded-lg space-y-3"
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
                  <select v-model="edu.attainment" class="input-field border">
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
                    class="input-field"
                  />
                </div>

                <!-- Year -->
                <div>
                  <label class="block font-medium mb-1">Year</label>
                  <input
                    v-model="edu.year"
                    type="text"
                    placeholder="e.g. 2020"
                    class="input-field"
                  />
                </div>
              </div>
            </div>
            <!-- Skills -->
            <div class="border rounded-xl p-4 space-y-3">
              <!-- Header -->
              <div class="flex justify-between items-center">
                <label class="text-lg font-semibold">Skills</label>
              </div>

              <!-- Skill Input -->
              <div class="flex gap-2">
                <input
                  v-model="newSkill"
                  @keyup.enter="addSkill"
                  type="text"
                  placeholder="Type a skill and press Enter"
                  class="input-field flex-1"
                />
                <button
                  type="button"
                  @click="addSkill"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Skills List -->
              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="(skill, index) in skills"
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

            <!-- Certificates -->
            <div class="border rounded-xl p-4 space-y-4 relative">
              <!-- Header with Plus Button -->
              <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">Certificates</h2>
                <button
                  type="button"
                  @click="addCertificate"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Certificate Items -->
              <div
                v-for="(cert, index) in certificates"
                :key="index"
                class="space-y-2 border p-3 rounded-lg relative"
              >
                <!-- Delete Button -->
                <button
                  type="button"
                  @click="removeCertificate(index)"
                  class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
                >
                  ✕
                </button>

                <!-- Certificate Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input
                    v-model="cert.title"
                    type="text"
                    placeholder="Certificate Title"
                    class="input-field"
                  />
                  <input
                    type="file"
                    accept="image/*"
                    class="file-input"
                    @change="handleFileUpload($event, index)"
                  />
                </div>

                <!-- Certificate Preview -->
                <div>
                  <div
                    v-if="cert.image"
                    class="h-32 w-full flex items-center justify-center border rounded-lg"
                  >
                    <img
                      :src="cert.image"
                      alt="Certificate Preview"
                      class="h-32 object-cover rounded-lg"
                    />
                  </div>
                  <div
                    v-else
                    class="h-32 w-full flex items-center justify-center border rounded-lg text-gray-400 text-sm"
                  >
                    Certificate Preview
                  </div>
                </div>
              </div>
            </div>

            <!-- URL -->
            <div>
              <input type="url" placeholder="URL" class="input-field" />
            </div>

            <!-- Save Button -->
            <button
              type="button"
              class="w-full py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
            >
              Save Resume
            </button>
          </form>
        </div>
      </div>
    </div>

    <!--Large screen-->
    <div class="min-h-screen p-6 font-poppins hidden lg:flex">
      <!-- Left Column -->
      <div
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center"
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
        <h2 class="text-xl font-semibold mb-6">Keiro Musician</h2>

        <!-- Buttons -->
        <div class="w-full flex flex-col gap-3">
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="router.push('/resumepage')"
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
            @click="router.push('/certificatespage')"
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
            @click="router.push('/calendarpage')"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 33 34"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M2.75 11.0697C2.75 9.18408 2.75 8.24128 3.33579 7.65549C3.92157 7.0697 4.86438 7.0697 6.75 7.0697H26.25C28.1356 7.0697 29.0784 7.0697 29.6642 7.65549C30.25 8.24128 30.25 9.18408 30.25 11.0697V13.1394C30.25 13.6108 30.25 13.8465 30.1036 13.9929C29.9571 14.1394 29.7214 14.1394 29.25 14.1394H3.75C3.2786 14.1394 3.04289 14.1394 2.89645 13.9929C2.75 13.8465 2.75 13.6108 2.75 13.1394V11.0697Z"
                fill="white"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2.75 27.1065C2.75 28.9921 2.75 29.9349 3.33579 30.5207C3.92157 31.1065 4.86438 31.1065 6.75 31.1065H26.25C28.1356 31.1065 29.0784 31.1065 29.6642 30.5207C30.25 29.9349 30.25 28.9921 30.25 27.1065V17.9672C30.25 17.4958 30.25 17.2601 30.1036 17.1136C29.9571 16.9672 29.7214 16.9672 29.25 16.9672H3.75C3.2786 16.9672 3.04289 16.9672 2.89645 17.1136C2.75 17.2601 2.75 17.4958 2.75 17.9672V27.1065ZM9.625 20.795C9.625 20.3236 9.625 20.0879 9.77145 19.9415C9.91789 19.795 10.1536 19.795 10.625 19.795H14.125C14.5964 19.795 14.8321 19.795 14.9786 19.9415C15.125 20.0879 15.125 20.3236 15.125 20.795V21.6229C15.125 22.0943 15.125 22.33 14.9786 22.4765C14.8321 22.6229 14.5964 22.6229 14.125 22.6229H10.625C10.1536 22.6229 9.91789 22.6229 9.77145 22.4765C9.625 22.33 9.625 22.0943 9.625 21.6229V20.795ZM9.77145 25.5972C9.625 25.7437 9.625 25.9794 9.625 26.4508V27.2786C9.625 27.75 9.625 27.9857 9.77145 28.1322C9.91789 28.2786 10.1536 28.2786 10.625 28.2786H14.125C14.5964 28.2786 14.8321 28.2786 14.9786 28.1322C15.125 27.9857 15.125 27.75 15.125 27.2786V26.4508C15.125 25.9794 15.125 25.7437 14.9786 25.5972C14.8321 25.4508 14.5964 25.4508 14.125 25.4508H10.625C10.1536 25.4508 9.91789 25.4508 9.77145 25.5972ZM17.875 20.795C17.875 20.3236 17.875 20.0879 18.0214 19.9415C18.1679 19.795 18.4036 19.795 18.875 19.795H22.375C22.8464 19.795 23.0821 19.795 23.2286 19.9415C23.375 20.0879 23.375 20.3236 23.375 20.795V21.6229C23.375 22.0943 23.375 22.33 23.2286 22.4765C23.0821 22.6229 22.8464 22.6229 22.375 22.6229H18.875C18.4036 22.6229 18.1679 22.6229 18.0214 22.4765C17.875 22.33 17.875 22.0943 17.875 21.6229V20.795ZM18.0214 25.5972C17.875 25.7437 17.875 25.9794 17.875 26.4508V27.2786C17.875 27.75 17.875 27.9857 18.0214 28.1322C18.1679 28.2786 18.4036 28.2786 18.875 28.2786H22.375C22.8464 28.2786 23.0821 28.2786 23.2286 28.1322C23.375 27.9857 23.375 27.75 23.375 27.2786V26.4508C23.375 25.9794 23.375 25.7437 23.2286 25.5972C23.0821 25.4508 22.8464 25.4508 22.375 25.4508H18.875C18.4036 25.4508 18.1679 25.4508 18.0214 25.5972Z"
                fill="white"
              />
              <path
                d="M9.625 4.24182L9.625 8.48362"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
              />
              <path
                d="M23.375 4.24182L23.375 8.48362"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
            <span>Calendar</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="router.push('/bookmarkpage')"
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
        <div class="p-6 bg-white shadow-lg rounded-2xl">
          <div class="absolute right-4 dropdown dropdown-end">
            <div tabindex="0" role="button" class="group mr-5">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5 flex-shrink-0 group-hover:hidden"
                viewBox="0 0 24 24"
                fill="dark-slate"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5 flex-shrink-0 group-hover:block hidden"
                viewBox="0 0 24 24"
                fill="gray"
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
              <li>Download Resume</li>
            </ul>
          </div>
          <h1 class="text-2xl font-bold mb-6">Resume Editor</h1>

          <form class="space-y-6">
            <!-- Personal Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input
                type="text"
                placeholder="First Name"
                class="input-field border p-2"
              />
              <input
                type="email"
                placeholder="Email"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Middle Name"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Mobile Number"
                class="input-field border p-2"
              />
              <input
                type="text"
                placeholder="Last Name"
                class="input-field border p-2"
              />

              <input
                type="text"
                placeholder="Address"
                class="input-field border p-2"
              />
            </div>

            <!-- Professional Summary -->
            <div class="border rounded-xl p-4 space-y-3">
              <label class="block text-lg font-semibold mb-1"
                >Professional Summary</label
              >
              <textarea rows="3" class="border input-field w-full"></textarea>
            </div>

            <!-- Professional Experience -->
            <div class="border rounded-xl p-4 space-y-4 relative">
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
                v-for="(exp, index) in experiences"
                :key="index"
                class="relative border p-3 rounded-lg"
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
                  v-model="exp.text"
                  rows="3"
                  placeholder="Describe your professional experience..."
                  class="input-field w-full"
                ></textarea>
              </div>
            </div>

            <!-- Education -->
            <div class="border rounded-xl p-4 space-y-4 relative">
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
                v-for="(edu, index) in educationList"
                :key="index"
                class="relative border p-3 rounded-lg space-y-3"
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
                  <select v-model="edu.attainment" class="input-field border">
                    <option value="" disabled>Select level</option>
                    <option>Elementary</option>
                    <option>Highschool</option>
                    <option>Post-Secondary Non-Tertiary</option>
                    <option>Bachelor's Degree</option>
                    <option>Master's Degree</option>
                    <option>Doctoral's Degree</option>
                    <option>Professional Degree</option>
                  </select>
                </div>

                <!-- University Name -->
                <div>
                  <label class="block font-medium mb-1">University</label>
                  <input
                    v-model="edu.university"
                    type="text"
                    placeholder="Enter university name"
                    class="input-field"
                  />
                </div>

                <!-- Year -->
                <div>
                  <label class="block font-medium mb-1">Year</label>
                  <input
                    v-model="edu.year"
                    type="text"
                    placeholder="e.g. 2020-2024"
                    class="input-field"
                  />
                </div>
              </div>
            </div>

            <!-- Skills -->
            <div class="border rounded-xl p-4 space-y-3">
              <!-- Header -->
              <div class="flex justify-between items-center">
                <label class="text-lg font-semibold">Skills</label>
              </div>

              <!-- Skill Input -->
              <div class="flex gap-2">
                <input
                  v-model="newSkill"
                  @keyup.enter="addSkill"
                  type="text"
                  placeholder="Type a skill and press Enter"
                  class="input-field flex-1"
                />
                <button
                  type="button"
                  @click="addSkill"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Skills List -->
              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="(skill, index) in skills"
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

            <!-- Certificates -->
            <div class="border rounded-xl p-4 space-y-4 relative">
              <!-- Header with Plus Button -->
              <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">Certificates</h2>
                <button
                  type="button"
                  @click="addCertificate"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <!-- Certificate Items -->
              <div
                v-for="(cert, index) in certificates"
                :key="index"
                class="space-y-2 border p-3 rounded-lg relative"
              >
                <!-- Delete Button -->
                <button
                  type="button"
                  @click="removeCertificate(index)"
                  class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
                >
                  ✕
                </button>

                <!-- Certificate Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <input
                    v-model="cert.title"
                    type="text"
                    placeholder="Certificate Title"
                    class="input-field"
                  />
                  <input
                    type="file"
                    accept="image/*"
                    class="file-input"
                    @change="handleFileUpload($event, index)"
                  />
                </div>

                <!-- Certificate Preview -->
                <div>
                  <div
                    v-if="cert.image"
                    class="h-32 w-full flex items-center justify-center border rounded-lg"
                  >
                    <img
                      :src="cert.image"
                      alt="Certificate Preview"
                      class="h-32 object-cover rounded-lg"
                    />
                  </div>
                  <div
                    v-else
                    class="h-32 w-full flex items-center justify-center border rounded-lg text-gray-400 text-sm"
                  >
                    Certificate Preview
                  </div>
                </div>
              </div>
            </div>

            <!-- URL -->
            <div>
              <input type="url" placeholder="URL" class="input-field" />
            </div>

            <!-- Save Button -->
            <button
              type="button"
              class="w-full py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
            >
              Save Resume
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

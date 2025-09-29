<script setup>
import { reactive, ref, onMounted } from "vue";
import jsPDF from "jspdf";
import axios from "axios";   // ✅ API calls
import { useRouter } from "vue-router";

const isModalOpen = ref(false);
const pdfUrl = ref(null);
const newSkill = ref("");
const router = useRouter();
const userName = ref("");

// ✅ Resume Data
const resume = reactive({
  summary: "",
  experience: [],
  education: [],
  skills: [],
  url: "", // professionalLink stored here
});

// ✅ User Form Data
const form = reactive({
  firstName: "",
  middleName: "",
  lastName: "",
  emailAddress: "",
  phoneNumber: "",
  address: "",
});

// --- Save Resume (summary + professionalLink) ---
async function saveResume() {
  try {
    const token = localStorage.getItem("token");
    const response = await axios.post(
      "http://127.0.0.1:8000/api/resume",
      {
        summary: resume.summary,
        professionalLink: resume.url,
      },
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    alert("Resume saved successfully!");
    console.log(response.data);
  } catch (error) {
    console.error("Error saving resume:", error.response?.data || error);
    alert("Failed to save resume.");
  }
}

// --- Load Resume on Mount ---
async function loadResume() {
  try {
    const token = localStorage.getItem("token");
    const { data } = await axios.get("http://127.0.0.1:8000/api/resume", {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (data) {
      resume.summary = data.summary || "";
      resume.url = data.professionalLink || "";
    }
  } catch (error) {
    console.error("Error loading resume:", error.response?.data || error);
  }
}

// --- Delete Resume ---
async function deleteResume() {
  try {
    const token = localStorage.getItem("token");
    await axios.delete("http://127.0.0.1:8000/api/resume", {
      headers: { Authorization: `Bearer ${token}` },
    });

    resume.summary = "";
    resume.url = "";
    alert("Resume deleted successfully!");
  } catch (error) {
    console.error("Error deleting resume:", error.response?.data || error);
    alert("Failed to delete resume.");
  }
}

// --- Education ---
function addEducation() {
  resume.education.push({ school: "", degree: "", year: "" });
}
function removeEducation(index) {
  resume.education.splice(index, 1);
}

// --- Experience ---
function addExperience() {
  resume.experience.push({ company: "", role: "", duration: "" });
}
function removeExperience(index) {
  resume.experience.splice(index, 1);
}

// --- Skills ---
function addSkill() {
  if (newSkill.value.trim() !== "") {
    resume.skills.push(newSkill.value.trim());
    newSkill.value = "";
  }
}
function removeSkill(index) {
  resume.skills.splice(index, 1);
}

// --- Section Header Helper ---
function sectionHeader(doc, title, x, y, pageWidth, margin) {
  doc.setFontSize(14);
  doc.setFont("helvetica", "bold");
  doc.text(title, margin, y);
  doc.setLineWidth(0.5);
  doc.line(margin, y + 2, pageWidth - margin, y + 2);
}

// --- Generate PDF ---
function generatePdf() {
  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const margin = 20;
  let y = 20;

  // Helper: safely add text with wrapping + auto page break
  function addWrappedText(text, x, y, maxWidth, lineHeight = 6) {
    const lines = doc.splitTextToSize(text, maxWidth);
    for (let i = 0; i < lines.length; i++) {
      if (y > pageHeight - margin) {
        doc.addPage();
        y = margin;
      }
      doc.text(lines[i], x, y);
      y += lineHeight; // ✅ single spacing
    }
    return y;
  }

  // Header
  doc.setFontSize(18);
  y = addWrappedText(`${form.firstName} ${form.lastName}`, margin, y, pageWidth - 2 * margin, 8);
  doc.setFontSize(11);
  y = addWrappedText(`${form.emailAddress} | ${form.phoneNumber}`, margin, y, pageWidth - 2 * margin, 6);
  y += 8;

  // Summary
  sectionHeader(doc, "Summary", margin, y, pageWidth, margin);
  y += 6;
  doc.setFontSize(11);
  y = addWrappedText(resume.summary || "", margin, y, pageWidth - 2 * margin, 6);

  // Education
  if (resume.education.length) {
    sectionHeader(doc, "Education", margin, y, pageWidth, margin);
    y += 6;
    resume.education.forEach((edu) => {
      y = addWrappedText(`${edu.degree} - ${edu.school} (${edu.year})`, margin, y, pageWidth - 2 * margin, 6);
    });
  }

  // Experience
  if (resume.experience.length) {
    sectionHeader(doc, "Experience", margin, y, pageWidth, margin);
    y += 6;
    resume.experience.forEach((exp) => {
      y = addWrappedText(`${exp.role} at ${exp.company} (${exp.duration})`, margin, y, pageWidth - 2 * margin, 6);
    });
  }

  // Skills
  if (resume.skills.length) {
    sectionHeader(doc, "Skills", margin, y, pageWidth, margin);
    y += 6;
    y = addWrappedText(resume.skills.join(", "), margin, y, pageWidth - 2 * margin, 6);
  }

  // ✅ create blob for modal preview
  const pdfBlob = doc.output("blob");
  pdfUrl.value = URL.createObjectURL(pdfBlob);
}

function generateAndOpenPdf() {
  generatePdf();
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

// --- Autofill user + resume data on mount ---
onMounted(() => {
  const savedUser = localStorage.getItem("user");
  if (savedUser) {
    try {
      const user = JSON.parse(savedUser);
      if (user.firstName && user.lastName) {
        userName.value = `${user.firstName} ${user.lastName}`;
        form.firstName = user.firstName || "";
        form.middleName = user.middleName || "";
        form.lastName = user.lastName || "";
        form.emailAddress = user.emailAddress || "";
        form.phoneNumber = user.phoneNumber || "";
        form.address = user.address || "";
      } else {
        userName.value = "Guest";
      }
    } catch (err) {
      console.error("Error parsing saved user:", err);
      userName.value = "Guest";
    }
  } else {
    userName.value = "Guest";
  }

  // ✅ load saved resume
  loadResume();
});

const logout = () => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Login" });
};
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <!--Large screen-->
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Left Column -->
      <div
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center hidden lg:flex"
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

            <span>Account Setting</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="logout"
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

      <div class="w-full lg:w-3/4 lg:pl-6 flex flex-col gap-6">
        <div class="bg-white p-4 rounded-lg">
          <h2 class="text-2xl font-bold mb-3">Resume Editor</h2>
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
                v-for="(exp, index) in resume.experience"
                :key="index"
                class="relative border p-3 rounded space-y-3"
              >
                <!-- Delete Button -->
                <button
                  type="button"
                  @click="removeExperience(index)"
                  class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
                >
                  ✕
                </button>
                <!-- Job Title -->
                <div>
                  <label class="block font-medium mb-1">Job Title</label>
                  <input
                    v-model="exp.jobTitle"
                    type="text"
                    placeholder="Enter job title"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- Company Name -->
                <div>
                  <label class="block font-medium mb-1">Company Name</label>
                  <input
                    v-model="exp.companyName"
                    type="text"
                    placeholder="Enter company name"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- Company Address -->
                <div>
                  <label class="block font-medium mb-1">Company Address</label>
                  <input
                    v-model="exp.companyAddress"
                    type="text"
                    placeholder="Enter company address"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- Start Date -->
                <div>
                  <label class="block font-medium mb-1">Start Date</label>
                  <input
                    v-model="exp.startDate"
                    type="number"
                    placeholder="e.g. 2020"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- End Date -->
                <div>
                  <label class="block font-medium mb-1">End Date</label>
                  <input
                    v-model="exp.endDate"
                    type="number"
                    placeholder="e.g. 2022"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
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
                <!-- Education Level -->
                <div>
                  <label class="block font-medium mb-1">Education Level</label>
                  <select
                    v-model="edu.educationLevel"
                    class="input-field border rounded w-full p-2"
                  >
                    <option value="" disabled>Select level</option>
                    <option>Elementary</option>
                    <option>High School</option>
                    <option>Senior High School</option>
                    <option>Bachelor's Degree</option>
                    <option>Master's Degree</option>
                    <option>Doctorate</option>
                    <option>Others</option>
                  </select>
                </div>
                <!-- Major -->
                <div>
                  <label class="block font-medium mb-1">Major</label>
                  <input
                    v-model="edu.major"
                    type="text"
                    placeholder="Enter major"
                    class="input-field border rounded w-full p-2 disabled:bg-gray-200"
                    :disabled="
                      edu.educationLevel === 'Elementary' ||
                      edu.educationLevel === 'High School' ||
                      edu.educationLevel === 'Senior High School'
                    "
                  />
                </div>
                <!-- Institution Name -->
                <div>
                  <label class="block font-medium mb-1">Institution Name</label>
                  <input
                    v-model="edu.institutionName"
                    type="text"
                    placeholder="Enter school/university name"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- Institution Address -->
                <div>
                  <label class="block font-medium mb-1"
                    >Institution Address</label
                  >
                  <input
                    v-model="edu.institutionAddress"
                    type="text"
                    placeholder="Enter address"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <!-- Graduation Date -->
                <div>
                  <label class="block font-medium mb-1">Graduation Year</label>
                  <input
                    v-model="edu.graduationDate"
                    type="number"
                    placeholder="e.g. 2025"
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
                  class="input-field flex-1 border rounded p-2"
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
            <div class="flex gap-4">
              <button
                type="button"
                @click="generateAndOpenPdf"
                class="w-1/2 py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
              >
                Preview Resume
              </button>
              <button
                type="button"
                class="w-1/2 py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
                @click="saveResume"
              >
                Save
              </button>
            </div>
          </form>
          <!-- PDF Preview Modal -->
          <div
            v-if="isModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-opacity-50 z-50"
          >
            <div
              class="bg-white rounded-lg shadow-lg max-w-4xl w-full relative"
            >
              <!-- Close Button -->
              <button
                @click="closeModal"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
              >
                ✕
              </button>
              <div class="p-4">
                <h2 class="text-xl font-semibold mb-4">Resume Preview</h2>
                <!-- PDF Preview -->
                <iframe
                  v-if="pdfUrl"
                  :src="pdfUrl"
                  class="w-full h-[500px] border"
                  frameborder="0"
                ></iframe>
                <!-- Download Button -->
                <div class="mt-4 flex justify-end">
                  <a
                    v-if="pdfUrl"
                    :href="pdfUrl"
                    download="resume.pdf"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                  >
                    Download PDF
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

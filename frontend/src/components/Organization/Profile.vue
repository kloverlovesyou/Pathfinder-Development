<template>
    <div class="organization-profile">
        <!-- Sidebar -->
        <transition name="slide">
            <aside class="sidebar" :class="{ collapsed: !isSidebarOpen }" @mouseenter="isSidebarOpen = true"
                @mouseleave="isSidebarOpen = false">
                <!-- Home -->
                <div class="icon" @click="$router.push('/OrganizationHomePage')">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.25 17.0585C6.25 16.0494 6.25 15.5448 6.47166 15.1141C6.69333 14.6833 7.1039 14.3901 7.92505 13.8035L13.8375 9.58034C14.3989 9.17938 14.6795 8.9789 15 8.9789C15.3205 8.9789 15.6011 9.17938 16.1625 9.58034L22.075 13.8035C22.8961 14.3901 23.3067 14.6833 23.5283 15.1141C23.75 15.5448 23.75 16.0494 23.75 17.0585V24.25C23.75 25.1928 23.75 25.6642 23.4571 25.9571C23.1642 26.25 22.6928 26.25 21.75 26.25H8.25C7.30719 26.25 6.83579 26.25 6.54289 25.9571C6.25 25.6642 6.25 25.1928 6.25 24.25V17.0585Z"
                            fill="white" />
                        <path
                            d="M3.75 15.6366C3.75 15.9035 3.75 16.0369 3.8341 16.0781C3.91819 16.1192 4.02352 16.0373 4.23418 15.8734L13.7721 8.45502C14.362 7.99625 14.6569 7.76686 15 7.76686C15.3431 7.76686 15.638 7.99625 16.2279 8.45502L25.7658 15.8734C25.9765 16.0373 26.0818 16.1192 26.1659 16.0781C26.25 16.0369 26.25 15.9035 26.25 15.6366V14.7282C26.25 14.2478 26.25 14.0076 26.1483 13.7997C26.0466 13.5918 25.857 13.4444 25.4779 13.1495L16.2279 5.95502C15.638 5.49625 15.3431 5.26686 15 5.26686C14.6569 5.26686 14.362 5.49625 13.7721 5.95502L4.52212 13.1495C4.14295 13.4444 3.95337 13.5918 3.85168 13.7997C3.75 14.0076 3.75 14.2478 3.75 14.7282V15.6366Z"
                            fill="white" />
                        <path
                            d="M16.125 18.75H13.875C12.7704 18.75 11.875 19.6454 11.875 20.75V26.1C11.875 26.1828 11.9422 26.25 12.025 26.25H17.975C18.0578 26.25 18.125 26.1828 18.125 26.1V20.75C18.125 19.6454 17.2296 18.75 16.125 18.75Z"
                            fill="white" />
                        <rect x="20" y="6.25" width="2.5" height="5" rx="0.5" fill="white" />
                    </svg>
                    <span>Home</span>
                </div>

                <!-- Trainings -->
                <div class="icon" @click="$router.push('/trainings')">
                    <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M20.5837 3.625C23.4119 3.625 24.8261 3.62526 25.7048 4.50391C26.5833 5.3826 26.5837 6.79675 26.5837 9.625V19.375C26.5837 22.2033 26.5833 23.6174 25.7048 24.4961C24.8261 25.3747 23.4119 25.375 20.5837 25.375H8.41666C5.58824 25.375 4.17425 25.3748 3.29557 24.4961C2.41689 23.6174 2.41666 22.2034 2.41666 19.375V9.625C2.41666 6.79657 2.41689 5.38259 3.29557 4.50391C4.17425 3.62523 5.58824 3.625 8.41666 3.625H20.5837ZM9.66666 12.292C9.11438 12.292 8.66666 12.7397 8.66666 13.292V20.542L8.67155 20.6445C8.72303 21.1485 9.1491 21.542 9.66666 21.542C10.1842 21.542 10.6103 21.1485 10.6618 20.6445L10.6667 20.542V13.292C10.6667 12.7397 10.2189 12.292 9.66666 12.292ZM19.3337 9.875C18.7814 9.875 18.3337 10.3227 18.3337 10.875V20.542L18.3385 20.6436C18.3896 21.148 18.8158 21.542 19.3337 21.542C19.8514 21.5418 20.2778 21.1479 20.3288 20.6436L20.3337 20.542V10.875C20.3337 10.3228 19.8858 9.87518 19.3337 9.875ZM14.4997 14.708C13.9476 14.7082 13.4998 15.156 13.4997 15.708V20.541L13.5046 20.6436C13.5557 21.1477 13.982 21.5408 14.4997 21.541C15.0175 21.541 15.4436 21.1478 15.4948 20.6436L15.4997 20.541V15.708C15.4995 15.1559 15.0518 14.708 14.4997 14.708Z"
                            fill="white" />
                    </svg>
                    <span>Trainings</span>
                </div>

                <!-- Post -->
                <div class="icon" @click="togglePostOptions">
                    <svg width="29" height="29" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M3.625 7.625C3.625 5.41586 5.41586 3.625 7.625 3.625H21.375C23.5841 3.625 25.375 5.41586 25.375 7.625V21.375C25.375 23.5841 23.5841 25.375 21.375 25.375H7.625C5.41586 25.375 3.625 23.5841 3.625 21.375V7.625Z"
                            stroke="white" stroke-width="2" />
                        <path d="M14.5 9.6665L14.5 19.3332" stroke="white" stroke-width="2" stroke-linecap="square"
                            stroke-linejoin="round" />
                        <path d="M19.3333 14.5L9.66666 14.5" stroke="white" stroke-width="2" stroke-linecap="square"
                            stroke-linejoin="round" />
                    </svg>
                    <span>Post</span>
                </div>

                <transition name="fade">
                    <div v-if="showPostOptions && isSidebarOpen" class="post-options">
                        <button @click="$router.push('/post-career')">Post Career</button>
                        <button @click="$router.push('/post-training')">Post Training</button>
                    </div>
                </transition>

                <!-- Career -->
                <div class="icon" @click="$router.push('/career')">
                    <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M22.8798 11.0484C23.4046 10.8642 23.9845 11.1431 24.1081 11.6855C24.4792 13.3134 24.4217 15.0018 23.9268 16.6191C23.4739 18.0989 22.6698 19.4685 21.5787 20.6449C21.2148 21.0372 20.6017 21.0197 20.2239 20.6408L14.9487 15.3495C14.4292 14.8284 14.6314 13.9436 15.3257 13.6999L22.8798 11.0484ZM13 4.0826C13 3.50231 13.4932 3.04057 14.0672 3.12592C15.8633 3.39302 17.5788 4.00579 19.085 4.93161C20.3794 5.72731 21.4793 6.72976 22.3343 7.87824C22.709 8.38157 22.4513 9.07932 21.8592 9.28718L14.3313 11.9301C13.6809 12.1584 13 11.6758 13 10.9865V4.0826Z"
                            fill="white" />
                        <path
                            d="M11.9511 13.2908C11.9512 13.5763 12.0581 13.8518 12.25 14.0632L19.2677 21.7886C19.6714 22.233 19.5963 22.9337 19.0759 23.2331C18.245 23.7112 17.354 24.0924 16.4209 24.365C14.5402 24.9144 12.5476 25.0087 10.6201 24.6394C8.69236 24.2701 6.88846 23.4478 5.36911 22.2468C3.84993 21.0459 2.66126 19.5025 1.90915 17.7537C1.15711 16.0048 0.864996 14.1043 1.05759 12.2205C1.25024 10.3365 1.92171 8.52693 3.01364 6.95288C4.1056 5.37884 5.58398 4.08845 7.31735 3.19605C8.43484 2.62073 9.63633 2.22318 10.8757 2.01305C11.4515 1.91541 11.9511 2.37876 11.9511 2.96284V13.2908Z"
                            fill="white" />
                    </svg>
                    <span>Career</span>
                </div>

                <!-- Profile -->
                <div class="icon" @click="$router.push('/profile')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M15.6001 9.6C15.6001 11.5882 13.9884 13.2 12.0001 13.2C10.0119 13.2 8.40015 11.5882 8.40015 9.6C8.40015 7.61177 10.0119 6 12.0001 6C13.9884 6 15.6001 7.61177 15.6001 9.6ZM14.4001 9.6C14.4001 10.9255 13.3256 12 12.0001 12C10.6747 12 9.60015 10.9255 9.60015 9.6C9.60015 8.27452 10.6747 7.2 12.0001 7.2C13.3256 7.2 14.4001 8.27452 14.4001 9.6Z"
                            fill="white" />
                        <path
                            d="M12.0001 15C8.11552 15 4.80571 17.297 3.54492 20.5152C3.85206 20.8202 4.1756 21.1087 4.51412 21.3792C5.45296 18.4246 8.39818 16.2 12.0001 16.2C15.6021 16.2 18.5473 18.4246 19.4862 21.3792C19.8247 21.1087 20.1482 20.8202 20.4554 20.5152C19.1946 17.2971 15.8848 15 12.0001 15Z"
                            fill="white" />
                    </svg>
                    <span>Profile</span>
                </div>

                <!-- Spacer pushes Signout to bottom -->
                <div class="spacer"></div>

                <!-- Sign Out -->
                <div class="spacer"></div> <!-- pushes signout down -->
                <div class="icon signout" @click="signOut">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign Out</span>
                </div>
            </aside>
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const showPostOptions = ref(false)
const isSidebarOpen = ref(false)

const togglePostOptions = () => {
    showPostOptions.value = !showPostOptions.value
}

const signOut = () => {
    // Replace this with your real logout logic
    console.log('Signed out')
}
</script>

<style scoped>
.organization-profile {
    display: flex;
    height: 100vh;
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4f4f4;
}

.sidebar {
    width: 200px;
    background-color: #44576D;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: start;
    padding: 20px 10px;
    transition: width 0.3s ease;
    overflow: hidden;
}

.sidebar.collapsed {
    width: 60px;
}

.sidebar .icon {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 20px 0;
    font-size: 16px;
    cursor: pointer;
    padding: 8px;
    border-radius: 8px;
    width: 100%;
    white-space: nowrap;
    transition: background-color 0.2s ease;
}

.sidebar .icon svg {
    flex-shrink: 0;
    width: 30px;
    height: 30px;
}

.sidebar .icon:hover {
    background-color: #4a5568;
}

.sidebar .icon span {
    transition: opacity 0.3s ease;
    opacity: 1;
}

.sidebar.collapsed .icon span {
    opacity: 0;
    pointer-events: none;
}

.post-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background-color: #2d3748;
    padding: 10px;
    border-radius: 8px;
    white-space: nowrap;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.post-options button {
    background-color: #44576D;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    text-align: left;
    font-size: 13px;
    width: 120px;
}

.post-options button:hover {
    background-color: #5a667d;
}

.spacer {
    flex: 1;
}

.sidebar .signout {
    margin-top: auto;
}
</style>

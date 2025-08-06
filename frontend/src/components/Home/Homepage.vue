<template>
  <div class="min-h-screen flex flex-col items-center font-inter">
    <ul class="list bg-base-100 rounded-box shadow-sm">
      <li class="p-4 pb-2 tracking-wide semi-bold text-lg">
        <p>Career and Training</p>
        <h1>Match Recommendation</h1>
      </li>
      <!--IF POST IS TRAINING-->
      <li class="list-row">
        <div>
          <div name="name">Dio Lupa</div>
          <div name="title" class="text-xs uppercase font-semibold opacity-60">
            Remaining Reason
          </div>
        </div>
        <p name="description" class="list-col-wrap text-xs">
          "Remaining Reason" became an instant hit, praised for its haunting
          sound and emotional depth. A viral performance brought it widespread
          recognition, making it one of Dio Lupa’s most iconic tracks.
        </p>

        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn m-1 btn-ghost btn-circle">
            <svg
              width="3"
              height="10"
              viewBox="0 0 3 10"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M2.09375 8.47781C2.09375 8.73633 2.00284 8.95792 1.82102 9.14258C1.6392 9.32724 1.41761 9.41815 1.15625 9.41531C0.900568 9.41815 0.681818 9.32724 0.5 9.14258C0.318182 8.95792 0.227273 8.73633 0.227273 8.47781C0.227273 8.22781 0.318182 8.01048 0.5 7.82582C0.681818 7.63832 0.900568 7.54315 1.15625 7.54031C1.32955 7.54315 1.48722 7.5886 1.62926 7.67667C1.77131 7.7619 1.88494 7.87553 1.97017 8.01758C2.05256 8.15678 2.09375 8.31019 2.09375 8.47781ZM2.09375 4.99734C2.09375 5.25586 2.00284 5.47745 1.82102 5.66211C1.6392 5.84677 1.41761 5.93768 1.15625 5.93484C0.900568 5.93768 0.681818 5.84677 0.5 5.66211C0.318182 5.47745 0.227273 5.25586 0.227273 4.99734C0.227273 4.74734 0.318182 4.53001 0.5 4.34535C0.681818 4.15785 0.900568 4.06268 1.15625 4.05984C1.32955 4.06268 1.48722 4.10813 1.62926 4.1962C1.77131 4.28143 1.88494 4.39506 1.97017 4.53711C2.05256 4.67631 2.09375 4.82972 2.09375 4.99734ZM2.09375 1.51687C2.09375 1.77539 2.00284 1.99698 1.82102 2.18164C1.6392 2.3663 1.41761 2.45721 1.15625 2.45437C0.900568 2.45721 0.681818 2.3663 0.5 2.18164C0.318182 1.99698 0.227273 1.77539 0.227273 1.51687C0.227273 1.26687 0.318182 1.04954 0.5 0.864879C0.681818 0.677379 0.900568 0.582209 1.15625 0.579368C1.32955 0.582209 1.48722 0.627663 1.62926 0.715732C1.77131 0.800959 1.88494 0.914595 1.97017 1.05664C2.05256 1.19585 2.09375 1.34925 2.09375 1.51687Z"
                fill="black"
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
      </li>
      <!--IF POST IS CAREER-->
      <li class="list-row">
        <div>
          <div name="name">Ellie Beilish</div>
          <div
            name="position"
            class="text-xs uppercase font-semibold opacity-60"
          >
            Bears of a fever
          </div>
        </div>
        <p nae="detailsAndInstructions" class="list-col-wrap text-xs">
          "Bears of a Fever" captivated audiences with its intense energy and
          mysterious lyrics. Its popularity skyrocketed after fans shared it
          widely online, earning Ellie critical acclaim.
        </p>
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn m-1 btn-ghost btn-circle">
            <svg
              width="3"
              height="10"
              viewBox="0 0 3 10"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M2.09375 8.47781C2.09375 8.73633 2.00284 8.95792 1.82102 9.14258C1.6392 9.32724 1.41761 9.41815 1.15625 9.41531C0.900568 9.41815 0.681818 9.32724 0.5 9.14258C0.318182 8.95792 0.227273 8.73633 0.227273 8.47781C0.227273 8.22781 0.318182 8.01048 0.5 7.82582C0.681818 7.63832 0.900568 7.54315 1.15625 7.54031C1.32955 7.54315 1.48722 7.5886 1.62926 7.67667C1.77131 7.7619 1.88494 7.87553 1.97017 8.01758C2.05256 8.15678 2.09375 8.31019 2.09375 8.47781ZM2.09375 4.99734C2.09375 5.25586 2.00284 5.47745 1.82102 5.66211C1.6392 5.84677 1.41761 5.93768 1.15625 5.93484C0.900568 5.93768 0.681818 5.84677 0.5 5.66211C0.318182 5.47745 0.227273 5.25586 0.227273 4.99734C0.227273 4.74734 0.318182 4.53001 0.5 4.34535C0.681818 4.15785 0.900568 4.06268 1.15625 4.05984C1.32955 4.06268 1.48722 4.10813 1.62926 4.1962C1.77131 4.28143 1.88494 4.39506 1.97017 4.53711C2.05256 4.67631 2.09375 4.82972 2.09375 4.99734ZM2.09375 1.51687C2.09375 1.77539 2.00284 1.99698 1.82102 2.18164C1.6392 2.3663 1.41761 2.45721 1.15625 2.45437C0.900568 2.45721 0.681818 2.3663 0.5 2.18164C0.318182 1.99698 0.227273 1.77539 0.227273 1.51687C0.227273 1.26687 0.318182 1.04954 0.5 0.864879C0.681818 0.677379 0.900568 0.582209 1.15625 0.579368C1.32955 0.582209 1.48722 0.627663 1.62926 0.715732C1.77131 0.800959 1.88494 0.914595 1.97017 1.05664C2.05256 1.19585 2.09375 1.34925 2.09375 1.51687Z"
                fill="black"
              />
            </svg>
          </div>
          <ul
            tabindex="0"
            class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
          >
            <li><a>Bookmark</a></li>
            <li><a>Apply</a></li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</template>

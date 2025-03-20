<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    function switch_summary(tabId) {
        const solarSummaryElements = document.getElementsByClassName("solar-summary");
        const reSummaryElements = document.getElementsByClassName("re-summary");

        if (tabId === "re_tab") {
            Array.from(solarSummaryElements).forEach(element => element.style.display = "none");
            Array.from(reSummaryElements).forEach(element => element.style.display = "grid");
        } else if (tabId === "so_tab") {
            Array.from(reSummaryElements).forEach(element => element.style.display = "none");
            Array.from(solarSummaryElements).forEach(element => element.style.display = "grid");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const tabContainer = document.querySelector("#summary-switcher");
        const tabs = tabContainer.querySelectorAll(".tab");

        tabs.forEach(function (tabElement) {
            tabElement.addEventListener("click", function () {
                if (tabElement.classList.contains("active")) return;

                const direction = tabElement.getAttribute("tab-direction");

                tabContainer.classList.remove("left", "right");
                tabContainer.classList.add(direction);

                tabContainer.querySelector(".tab.active").classList.remove("active");
                tabElement.classList.add("active");

                switch_summary(tabElement.id);
            });
        });
    });
</script>
<script>
    function switch_user_tasks(tabChoice) {
        const meCards = document.getElementById("grid_tasks_me");
        const otherCards = document.getElementById("grid_tasks_all");

        // Toggle display based on the selected tab
        if (tabChoice === "me_tab") {
            meCards.style.display = "grid";
            otherCards.style.display = "none";
        } else if (tabChoice === "oth_tab") {
            meCards.style.display = "none";
            otherCards.style.display = "grid";
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const tabContainer = document.querySelector("#tasks-switcher");
        const tabs = tabContainer.querySelectorAll(".tab");

        tabs.forEach(function (tabElement) {
            tabElement.addEventListener("click", function () {
                if (tabElement.classList.contains("active")) return;

                const direction = tabElement.getAttribute("tab-direction");

                tabContainer.classList.remove("left", "right");
                tabContainer.classList.add(direction);

                tabContainer.querySelector(".tab.active").classList.remove("active");
                tabElement.classList.add("active");

                switch_user_tasks(tabElement.id);
            });
        });
    });
</script>

<script>
    function switch_performance(tabChoice) {
        const todaySection = document.getElementById("daily");
        const weekSection = document.getElementById("weekly");
        const monthSection = document.getElementById("monthly");

        // Toggle display based on the selected tab
        if (tabChoice === "today_tab") {
            todaySection.style.display = "grid";
            weekSection.style.display = "none";
            monthSection.style.display = "none";
        } else if (tabChoice === "week_tab") {
            weekSection.style.display = "grid";
            todaySection.style.display = "none";
            monthSection.style.display = "none";
        }else if (tabChoice === "month_tab") {
            todaySection.style.display = "none";
            weekSection.style.display = "none";
            monthSection.style.display = "grid";
        }
    }
  
    document.addEventListener("DOMContentLoaded", function () {
        const tabContainer = document.querySelector("#performance-switcher");
        const tabs = tabContainer.querySelectorAll(".tab");

        tabs.forEach(function (tabElement) {
            tabElement.addEventListener("click", function () {
                if (tabElement.classList.contains("active")) return;

                const direction = tabElement.getAttribute("tab-direction");

                tabContainer.classList.remove("left", "right");
                tabContainer.classList.add(direction);

                tabContainer.querySelector(".tab.active").classList.remove("active");
                tabElement.classList.add("active");
                switch_performance(tabElement.id);
            });
        });
    });
</script>

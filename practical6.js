
document.addEventListener("DOMContentLoaded", () => {
    const datasetSelect = document.getElementById("dataset-select");
    const searchInput = document.getElementById("search-input");
    const categoryFilter = document.getElementById("category-filter");
    const sortSelect = document.getElementById("sort-select");
    const dataList = document.getElementById("data-list");
    const loading = document.getElementById("loading");
    const error = document.getElementById("error");
    const resultCount = document.getElementById("result-count");
    const previousBtn = document.getElementById("previous-page");
    const nextBtn = document.getElementById("next-page");
    const pageInfo = document.getElementById("page-info");

    if (!datasetSelect || !searchInput || !categoryFilter ||
        !sortSelect || !dataList || !loading || !error ||
        !resultCount || !previousBtn || !nextBtn || !pageInfo) {
        console.error("Practical 6 HTML elements are missing.");
        return;
    }

    const files = {
        events: "events.json",
        students: "students.json",
        faqs: "faqs.json"
    };

    const datasets = {};
    let currentPage = 1;
    const pageSize = 5;

    async function loadData() {
        loading.textContent = "Loading data...";
        loading.style.display = "block";
        error.style.display = "none";
        dataList.replaceChildren();

        try {
            const entries = Object.entries(files);

            const results = await Promise.all(
                entries.map(async ([key, file]) => {
                    const response = await fetch(file);

                    if (!response.ok) {
                        throw new Error("Unable to load " + file);
                    }

                    const data = await response.json();

                    if (!Array.isArray(data)) {
                        throw new Error(file + " must contain a JSON array.");
                    }

                    return [key, data];
                })
            );

            results.forEach(([key, data]) => {
                datasets[key] = data;
            });

            loading.style.display = "none";
            renderCategories();
            renderData();
        } catch (err) {
            loading.style.display = "none";
            error.textContent =
                "Could not load data. Please run the project using Live Server and check your JSON files.";
            error.style.display = "block";
            console.error("Fetch error:", err);
        }
    }

    function getCategory(item, type) {
        if (type === "students") return item.course;
        return item.category;
    }

    function renderCategories() {
        const type = datasetSelect.value;
        const items = datasets[type] || [];

        categoryFilter.replaceChildren();

        const allOption = document.createElement("option");
        allOption.value = "all";
        allOption.textContent = "All Categories";
        categoryFilter.appendChild(allOption);

        const categories = [...new Set(
            items.map(item => getCategory(item, type)).filter(Boolean)
        )].sort((a, b) => a.localeCompare(b));

        categories.forEach(category => {
            const option = document.createElement("option");
            option.value = category;
            option.textContent = category;
            categoryFilter.appendChild(option);
        });
    }

    function getSearchText(item) {
    return Object.values(item)
        .flatMap(value => Array.isArray(value) ? value : [value])
        .join(" ")
        .toLowerCase();
}

    function getTitle(item, type) {
        if (type === "students") return item.name;
        if (type === "faqs") return item.question;
        return item.title;
    }

    function createCard(item, type) {
        const card = document.createElement("article");
        card.className = "practical6-card";

        const title = document.createElement("h3");
        title.textContent = getTitle(item, type) || "Untitled";
        card.appendChild(title);

        Object.entries(item).forEach(([key, value]) => {
            if (key === "id" || key === "title" ||
                key === "name" || key === "question") {
                return;
            }

            const detail = document.createElement("p");
            const label = key.charAt(0).toUpperCase() + key.slice(1);
            const content = Array.isArray(value)
                ? value.join(", ")
                : String(value);

            detail.textContent = label + ": " + content;
            card.appendChild(detail);
        });

        return card;
    }

    function renderData() {
        const type = datasetSelect.value;
        const items = datasets[type] || [];
        const searchTerm = searchInput.value.trim().toLowerCase();
        const selectedCategory = categoryFilter.value;
        const sortOrder = sortSelect.value;

        let filtered = items.filter(item => {
            const matchesSearch = getSearchText(item).includes(searchTerm);
            const matchesCategory =
                selectedCategory === "all" ||
                getCategory(item, type) === selectedCategory;

            return matchesSearch && matchesCategory;
        });

        filtered.sort((a, b) => {
            const first = String(getTitle(a, type) || "").toLowerCase();
            const second = String(getTitle(b, type) || "").toLowerCase();

            return sortOrder === "desc"
                ? second.localeCompare(first)
                : first.localeCompare(second);
        });

        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));

        currentPage = Math.min(currentPage, totalPages);

        const start = (currentPage - 1) * pageSize;
        const pageItems = filtered.slice(start, start + pageSize);

        dataList.replaceChildren();

        pageItems.forEach(item => {
            dataList.appendChild(createCard(item, type));
        });

        resultCount.textContent =
            "Showing " + pageItems.length + " of " + filtered.length + " records";

        pageInfo.textContent =
            "Page " + currentPage + " of " + totalPages;

        previousBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        if (filtered.length === 0) {
            const message = document.createElement("p");
            message.textContent = "No matching records found.";
            dataList.appendChild(message);
        }
    }

    datasetSelect.addEventListener("change", () => {
        currentPage = 1;
        searchInput.value = "";
        renderCategories();
        renderData();
    });

    searchInput.addEventListener("input", () => {
        currentPage = 1;
        renderData();
    });

    categoryFilter.addEventListener("change", () => {
        currentPage = 1;
        renderData();
    });

    sortSelect.addEventListener("change", () => {
        currentPage = 1;
        renderData();
    });

    previousBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderData();
        }
    });

    nextBtn.addEventListener("click", () => {
        const items = datasets[datasetSelect.value] || [];
        if (currentPage < Math.ceil(items.length / pageSize)) {
            currentPage++;
            renderData();
        }
    });

    loadData();
});

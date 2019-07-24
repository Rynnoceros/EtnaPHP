export default {
    name:"DatabaseStatsComponent",
    props: ["selected_database", "target"],
    data() {
        return {
            statistics: null,
        }
    },
    methods: {
        show_statistics() {
            if (this.selected_database) {
                fetch("http://rynnoceros.etnaformation.local/backend/databases/show_stats.php", {
                    method: "POST",
                    body: JSON.stringify({ "database_name" : this.selected_database })
                })
                .then(response => response.json())
                .then(json_data => {
                    this.statistics = json_data.data;
            });
            }
        }
    },
    created() {
        this.show_statistics();
    },
}
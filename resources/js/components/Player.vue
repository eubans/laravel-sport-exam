<template>
    <main>
        <loading v-model:active="isLoading"
            :is-full-page="fullPage"
        />

        <h1 v-if="isPageLoaded" :style="{color: colorTheme}">{{ team == 'allblacks' ? "All Blacks Rugby" : "NBA Basketball"}}</h1>
        <div class="card" v-if="isPageLoaded" :style="{borderTopColor: colorTheme}">
            <div class="image">
                <img v-if="team == 'allblacks'" :src="'/images/teams/allblacks.png' + '?v' + player.id" alt="All blacks logo" class="logo" />
                <img v-else :src="(player.current_team == 'GSW' ? '/images/teams/gsw.png' : '/images/teams/mem.png') + '?v' + player.id" alt="NBA logo" class="logo" />
            </div>
            <div class="name">
                <em>#{{ player.number }}</em>
                <h2>{{ player.first_name }} <strong>{{ player.last_name }}</strong></h2>
            </div>
            <div class="profile">
                <img :src="('/images/players/' + (team == 'allblacks' ? 'allblacks/' : 'nba/') + player.image) + '?v' + player.id" :alt="player.first_name + ' ' + player.last_name" class="headshot" />
                <div class="features">
                    <div class="feature" v-for="featured in player.featured" :key="featured.value">
                        <h3>{{ featured.label }}</h3>
                        {{ featured.value }}
                    </div>
                </div>
            </div>
            <div class="bio">
                <div class="data">
                    <strong>Position</strong>
                    {{ player.position }}
                </div>
                <div class="data">
                    <strong>Weight</strong>
                    {{ player.weight }}KG
                </div>
                <div class="data">
                    <strong>Height</strong>
                    {{ player.height }}CM
                </div>
                <div class="data">
                    <strong>Age</strong>
                    {{ player.age }} years
                </div>
            </div>
            <div class="navigation">
                <div class="colored" :style="{backgroundColor: colorTheme}">
                    <a @click="getPlayer(player.prev_player.id)">{{ player.prev_player['name'] }}</a>
                </div>
                <div>
                    {{ player.first_name }} {{ player.last_name }}
                </div>
                <div class="colored" :style="{backgroundColor: colorTheme}">
                    <a @click="getPlayer(player.next_player.id)">{{ player.next_player['name'] }}</a>
                </div>
            </div>
        </div>
    </main>
</template>
<script>
    import axios from 'axios';
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        components: {
            Loading
        },
        data() {
            return {
                team: 'allblacks',
                colorTheme: '#000000',
                isPageLoaded: false,
                isLoading: false,
                fullPage: true,
                players: [],

                player : {
                    number : "",
                    first_name : "",
                    last_name : "",
                    featured : [],
                    position : "",
                    weight : "",
                    height : "",
                    age : "",
                    sport : "",
                    next_player : {
                        id: "",
                        name: ""
                    },
                    prev_player : {
                        id: "",
                        name: ""
                    },
                }
            };
        },
        mounted() {
            this.team = this.$route.params.team;

            // change the theme
            if(this.team == 'nba')
                this.colorTheme = '#1D4487';

            this.getAllPlayers();
        },
        methods: {
            getAllPlayers(){
                this.isLoading = true;

                axios.get("/api/"+this.team,
                ).then(response => {
                    if(response.data){
                        this.players = response.data;

                        // will call the player by id
                        this.getPlayer(this.$route.params.id);
                    }

                    this.isPageLoaded = true;
                    this.isLoading = false;
                }).catch(e => {
                    console.error(e);
                });
            },
            getPlayer(id) {
                this.player = this.players.filter(player => {
                    if(player.id === parseInt(id))
                        return player;
                })[0];

                if(this.player === undefined){
                    this.player = this.players[0];
                    id = 1;
                }

                window.history.pushState('', '', '/' + this.team + '/' + id);
            }
        }
    };
</script>

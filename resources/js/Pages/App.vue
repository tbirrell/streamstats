<template>
    <div class="container mx-auto px-4 py-2">
        <h1 class="font-medium leading-tight text-5xl mt-0 mb-2 text-blue-600">StreamStats</h1>
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600">Top 100 Streams</h2>
        <EasyDataTable
            buttons-pagination
            theme-color="#2563eb"
            :rows-per-page="10"
            :headers="top100StreamsHeaders"
            :items="top100Streams"
        />
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600">Stream Count By Start Time</h2>
        <EasyDataTable
            buttons-pagination
            theme-color="#2563eb"
            :rows-per-page="10"
            :headers="streamsByStartTimeHeaders"
            :items="streamsByStartTime"
            sort-by="hourSort"
        />
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600"></h2>
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600"></h2>
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600"></h2>
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600"></h2>
    </div>
    <div class="container mx-auto px-4 py-2">
        <h2 class="font-medium leading-tight text-3xl mt-0 mb-2 text-blue-600"></h2>
    </div>
</template>

<script>
import {DateTime} from 'luxon';
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

export default {
    props: {
        totalStreamsByGame: Object,
        topGamesByViewer: Object,
        medianViewers: Number,
        top100Streams: Array,
        allStreams: Array,
        userStreams: Array,
        tags: Object
    },
    data: () => ({
        top100StreamsHeaders: [
            {text: "Channel", value: "channel_name"},
            {text: "Title", value: "stream_title"},
            {text: "Game", value: "game"},
            {text: "Viewer Count", value: "viewer_count", sortable: true},
            // {text: "", value: "tags" },
            {text: "Start Time", value: "start_time"}
        ],
        streamsByStartTimeHeaders: [{text: "Started", value: "hours"}, {text: "Stream Count", value: "count"}]
    }),
    computed: {
        streamsByStartTime() {
            var res = {};
            this.allStreams.forEach(function (stream) {
                let end = DateTime.now().setZone("utc");
                let start = DateTime.fromSQL(stream.start_time, {zone: "utc"});
                let diff = end.diff(start, ['hours', 'minutes']);

                let key = (diff.minutes >= 30) ? diff.hours + 1 : diff.hours;

                if (!res.hasOwnProperty(key)) {
                    res[key] = 1;
                } else {
                    res[key]++;
                }
            });

            let sorted = []
            Object.keys(res).map(function (key) {
                sorted[key] = {
                    hours: this.textTimeAgo(parseInt(key)),
                    hourSort: parseInt(key),
                    count: res[key]
                };
            }, this);
            return sorted;
        },
        followedTop1000() {
            let followedTop1000 = [];

            this.userStreams.forEach(function (userStream) {
                let s = this.allStreams.find(function (topStream) {
                    return parseInt(topStream.stream_id) === parseInt(userStream.id);
                });

                if (typeof s !== 'undefined') {
                    followedTop1000.push(userStream);
                }
            }, this);

            return followedTop1000;
        },
        lowestStreamNeeds() {
            let userStream = this.userStreams.reduce(function (prev, curr) {
                return prev.viewer_count < curr.viewer_count ? prev : curr;
            });
            let top1000 = this.top100Streams.reduce(function (prev, curr) {
                return prev.viewer_count < curr.viewer_count ? prev : curr;
            });

            return top1000.viewer_count - userStream.viewer_count;
        },
        sharedTags() {
            let myTags = [];

            this.userStreams.forEach(function (userStream) {
                myTags = [...myTags, ...userStream.tag_ids];
            }, this);

            myTags = myTags.filter((value, index, self) => {
                return self.indexOf(value) === index;
            });

            let sharedTags = myTags.map((tag) => {
                return this.tags[tag];
            }).filter(Boolean);

            return sharedTags;
        },
        top100WithFormatedDate() {
            this.top100Streams.map((stream) => {
                let end = DateTime.now().setZone("utc");
                let start = DateTime.fromSQL(stream.start_time, {zone: "utc"});
                let diff = end.diff(start, ['hours', 'minutes']);

                let hours = (diff.minutes >= 30) ? diff.hours + 1 : diff.hours;

                stream.start_time = this.textTimeAgo(hours);

                return stream;
            });
        }
    },
    methods: {
        textTimeAgo(value) {
            switch (value) {
                case 0:
                    return 'Started recently';
                case 1:
                    return 'About 1 hour ago';
                default:
                    return 'About ' + value + ' hours ago'
            }
        }
    },
    components: {
        EasyDataTable: Vue3EasyDataTable
    }
};
</script>

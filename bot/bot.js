const Discord = require('discord.js');
const Enmap = require("enmap");
const fs = require("fs");
const config = require('./config.json');

const client = new Discord.Client();
client.login(config.botToken);
// We also need to make sure we're attaching the config to the CLIENT so it's accessible everywhere!
client.config = config;

client.on('ready', () => {
    client.user.setActivity(`planning for ${client.guilds.size} guilds`);
});

client.on('guildCreate', guild => {
    client.user.setActivity(`planning for ${client.guilds.size} guilds`);
});
client.on('guildDelete', guild => {
    client.user.setActivity(`planning for ${client.guilds.size} guilds`);
});

fs.readdir("./events", (err, files) => {
    if (err) return console.error(err);

    files.forEach(file => {
        if (!file.endsWith(".js")) return;

        const event = require(`./events/${file}`);
        let eventName = file.split(".")[0];

        client.on(eventName, event.bind(null, client));
        delete require.cache[require.resolve(`./events/${file}`)];
    });
});
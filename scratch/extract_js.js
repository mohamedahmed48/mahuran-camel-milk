const fs = require('fs');
const content = fs.readFileSync('layout.php', 'utf8');
const match = content.match(/<script>([\s\S]*?)<\/script>/g);
if (match) {
    match.forEach((script, index) => {
        const jsCode = script.replace(/<\/?script>/g, '');
        // strip PHP tags
        const cleanJs = jsCode.replace(/<\?php[\s\S]*?\?>/g, '""');
        fs.writeFileSync(`scratch/script_${index}.js`, cleanJs);
        console.log(`Extracted script_${index}.js`);
    });
}

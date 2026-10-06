const fs = require('fs');
const path = require('path');

const iconIds = [
  '10:1038', '10:1044', '10:1059', '10:1064', '10:1077', '10:1082',
  '10:1095', '10:1100', '10:1113', '10:1118', '10:1133', '10:1155',
  '10:1166', '10:1242', '10:1251', '10:1294', '10:1301', '10:1310',
  '10:1313', '10:1361', '10:1428', '10:1487', '10:1492', '10:1497',
  '10:1508', '10:1513', '10:1518', '10:1523', '10:1528', '10:1533', '10:1548'
];

const iconNames = {
  '10:1038': 'download',
  '10:1044': 'plus',
  '10:1059': 'trend_up',
  '10:1064': 'orders_clipboard',
  '10:1077': 'warning_exclamation',
  '10:1082': 'hourglass',
  '10:1095': 'check_circle',
  '10:1100': 'wallet',
  '10:1113': 'user_plus',
  '10:1118': 'users',
  '10:1133': 'search',
  '10:1155': 'clock',
  '10:1166': 'chevron_right',
  '10:1242': 'arrow_right',
  '10:1251': 'truck',
  '10:1294': 'chevron_right_orange',
  '10:1301': 'calendar',
  '10:1310': 'chevron_left',
  '10:1313': 'chevron_right_calendar',
  '10:1361': 'calendar_date_15',
  '10:1428': 'history',
  '10:1487': 'header_calendar',
  '10:1492': 'bell',
  '10:1497': 'header_user',
  '10:1508': 'nav_beranda',
  '10:1513': 'nav_produk',
  '10:1518': 'nav_pesanan',
  '10:1523': 'nav_pelanggan',
  '10:1528': 'nav_laporan',
  '10:1533': 'nav_pengaturan',
  '10:1548': 'logout'
};

async function run() {
  const targetDir = path.join(__dirname, '../public/assets/icons');
  if (!fs.existsSync(targetDir)) {
    fs.mkdirSync(targetDir, { recursive: true });
  }

  const idsParam = iconIds.join(',');
  const url = `https://api.figma.com/v1/images/GjBMQm8Tx1224HszJQeoa9?ids=${idsParam}&format=svg`;
  console.log('Fetching SVGs from Figma...');
  const res = await fetch(url, {
    headers: { 'X-Figma-Token': process.env.FIGMA_ACCESS_TOKEN || '' }
  });
  const data = await res.json();
  const images = data.images;

  for (const id of iconIds) {
    const imgUrl = images[id];
    const name = iconNames[id] || id.replace(':', '_');
    if (imgUrl) {
      const svgRes = await fetch(imgUrl);
      const svgText = await svgRes.text();
      fs.writeFileSync(path.join(targetDir, `${name}.svg`), svgText);
      console.log(`Saved ${name}.svg`);
    } else {
      console.log(`No SVG for ${id}`);
    }
  }
}

run().catch(console.error);

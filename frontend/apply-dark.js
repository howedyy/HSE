import fs from 'fs';
import path from 'path';

const walkSync = (dir, filelist = []) => {
  fs.readdirSync(dir).forEach(file => {
    const dirFile = path.join(dir, file);
    try {
      filelist = walkSync(dirFile, filelist);
    } catch (err) {
      if (err.code === 'ENOTDIR' || err.code === 'EBADF') {
        if (dirFile.endsWith('.tsx') || dirFile.endsWith('.ts')) {
          filelist.push(dirFile);
        }
      } else {
        throw err;
      }
    }
  });
  return filelist;
};

const applyDarkClasses = () => {
  const files = walkSync(path.join(process.cwd(), 'src/presentation'));
  
  files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // Avoid double applying by checking if dark: variant already exists right after or somewhere nearby,
    // but the simplest way is a regex that replaces only if the dark: variant isn't already there.
    const replacements = [
      { pattern: /bg-white(?!\s+dark:bg-slate)/g, replacement: 'bg-white dark:bg-slate-900' },
      { pattern: /border-gray-100(?!\s+dark:border-slate)/g, replacement: 'border-gray-100 dark:border-slate-800' },
      { pattern: /border-gray-200(?!\s+dark:border-slate)/g, replacement: 'border-gray-200 dark:border-slate-700' },
      { pattern: /text-gray-800(?!\s+dark:text-gray)/g, replacement: 'text-gray-800 dark:text-gray-200' },
      { pattern: /text-gray-900(?!\s+dark:text-gray)/g, replacement: 'text-gray-900 dark:text-gray-100' },
      { pattern: /text-gray-700(?!\s+dark:text-gray)/g, replacement: 'text-gray-700 dark:text-gray-300' },
      { pattern: /text-gray-600(?!\s+dark:text-gray)/g, replacement: 'text-gray-600 dark:text-gray-400' },
      { pattern: /text-gray-500(?!\s+dark:text-slate)/g, replacement: 'text-gray-500 dark:text-slate-400' },
      { pattern: /bg-gray-50(?!\s+dark:bg-slate)/g, replacement: 'bg-gray-50 dark:bg-slate-950/50' },
      { pattern: /bg-gray-100(?!\s+dark:bg-slate)/g, replacement: 'bg-gray-100 dark:bg-slate-800' }
    ];

    let newContent = content;
    for (const { pattern, replacement } of replacements) {
      newContent = newContent.replace(pattern, replacement);
    }
    
    // specifically for MainLayout, since we already did it, the negative lookaheads will prevent double dark classes.
    
    if (newContent !== content) {
      fs.writeFileSync(file, newContent, 'utf8');
      console.log(`Updated ${file}`);
    }
  });
};

applyDarkClasses();

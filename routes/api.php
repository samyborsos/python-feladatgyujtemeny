Route::get('/questions/search', function (Request $request) {
$query = Question::query();

// Apply search
if ($search = $request->input('q')) {
$query->where(function($q) use ($search) {
$q->where('title_en', 'like', "%{$search}%")
->orWhere('description_en', 'like', "%{$search}%");
});
}

// Apply difficulty filter
if ($difficulties = $request->input('difficulty')) {
$query->whereIn('difficulty', explode(',', $difficulties));
}

// Apply sorting
match ($request->input('sort')) {
'newest' => $query->latest(),
'oldest' => $query->oldest(),
'difficulty-asc' => $query->orderBy('difficulty', 'asc'),
'difficulty-desc' => $query->orderBy('difficulty', 'desc'),
default => $query->latest()
};

return $query->paginate(30);
});
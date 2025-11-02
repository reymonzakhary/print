export interface Category {
  id: number;
  name: string;
}

export interface Option {
  id: number;
  name: string;
  box: string;
}

export type QueryToken =
  | { type: "category"; text: string; data: Category }
  | { type: "option"; text: string; data: Option }
  | { type: "text"; text: string };

type SearchItem = {
  term: string;
  type: "category" | "searchedcategory" | "option";
  data: Category | Option;
};

type Match = {
  start: number;
  end: number;
  token: QueryToken;
};

/**
 * Creates SearchItems from a collection of entities
 *
 * @param entities - Collection of entities (categories or options)
 * @param type - Type of the search item
 * @param nameField - The field to use as the search term (default: 'name')
 * @returns Array of SearchItems
 */
const createSearchItems = <T extends { id: number } & Record<string, any>>(
  entities: T[],
  type: "category" | "searchedcategory" | "option",
  nameField: string = "name",
): SearchItem[] =>
  entities.map((entity) => ({
    term: entity[nameField],
    type,
    data: entity as any, // Cast is necessary due to the heterogeneous nature of the data
  }));

/**
 * Sorts search items by term length in descending order
 *
 * @param searchItems - Array of search items to sort
 * @returns Sorted array of search items
 */
const sortByTermLength = (searchItems: SearchItem[]): SearchItem[] =>
  [...searchItems].sort((a, b) => b.term.length - a.term.length);

/**
 * Escapes special characters in a string for use in a RegExp
 *
 * @param input - String to escape
 * @returns Escaped string
 */
const escapeRegExp = (input: string): string => input.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

/**
 * Creates a regex for whole word, case-insensitive matching
 *
 * @param term - Term to match
 * @returns RegExp for matching the term
 */
const createWordBoundaryRegex = (term: string): RegExp =>
  new RegExp(`\\b${escapeRegExp(term)}\\b`, "gi");

/**
 * Checks if a potential match overlaps with any existing matches
 *
 * @param newMatch - The potential match to check
 * @param existingMatches - Array of existing matches
 * @returns True if overlapping, false otherwise
 */
const isOverlapping = (start: number, end: number, existingMatches: Match[]): boolean =>
  existingMatches.some((match) => start < match.end && end > match.start);

/**
 * Finds all matches for a search item in the query
 *
 * @param query - The query string
 * @param searchItem - The search item to find matches for
 * @param existingMatches - Array of already found matches
 * @returns Array of new matches
 */
const findMatchesForSearchItem = (
  query: string,
  searchItem: SearchItem,
  existingMatches: Match[],
): Match[] => {
  const matches: Match[] = [];
  const regex = createWordBoundaryRegex(searchItem.term);

  let match: RegExpExecArray | null;
  while ((match = regex.exec(query)) !== null) {
    const start = match.index;
    const end = regex.lastIndex;

    if (!isOverlapping(start, end, existingMatches)) {
      matches.push({
        start,
        end,
        token: {
          type: searchItem.type === "searchedcategory" ? "category" : searchItem.type,
          text: match[0],
          data: searchItem.data,
        } as QueryToken,
      });
    }
  }

  return matches;
};

/**
 * Finds all matches for all search items in the query
 *
 * @param query - The query string
 * @param searchItems - Array of search items
 * @returns Array of matches
 */
const findAllMatches = (query: string, searchItems: SearchItem[]): Match[] => {
  const allMatches: Match[] = [];

  for (const searchItem of searchItems) {
    const newMatches = findMatchesForSearchItem(query, searchItem, allMatches);
    allMatches.push(...newMatches);
  }

  return allMatches.sort((a, b) => a.start - b.start);
};

/**
 * Creates text tokens for parts of the query not covered by matches
 *
 * @param query - The query string
 * @param matches - Array of matches
 * @returns Array of all tokens including text tokens
 */
const createTokensWithTextSegments = (query: string, matches: Match[]): QueryToken[] => {
  const tokens: QueryToken[] = [];
  let currentIndex = 0;

  for (const match of matches) {
    if (match.start > currentIndex) {
      // Add text token for the gap
      tokens.push({
        type: "text",
        text: query.slice(currentIndex, match.start),
      });
    }

    // Add the match token
    tokens.push(match.token);
    currentIndex = match.end;
  }

  // Add any remaining text after the last match
  if (currentIndex < query.length) {
    tokens.push({
      type: "text",
      text: query.slice(currentIndex),
    });
  }

  return tokens;
};

/**
 * Returns an array of tokens extracted from the query.
 *
 * For every category or option that appears in the query (using case-insensitive word-boundary matching),
 * a token is returned. The token contains the recognized text and the full object.
 *
 * @param query - The input query string.
 * @param categories - Array of category objects.
 * @param categorySearchResult - Array of searched category objects.
 * @param options - Array of option objects.
 * @param locale - Locale string for determining field names.
 * @returns An array of QueryToken objects.
 */
export function useTokenizeQuery(
  query: string,
  categories: Category[],
  categorySearchResult: any[], // Using any[] since the type is not fully specified in original
  options: Option[],
  locale: string,
): QueryToken[] {
  // Create search items for each entity type
  const categoryItems = createSearchItems(categories, "category");
  const searchedCategoryItems = createSearchItems(categorySearchResult, "searchedcategory", locale);
  const optionItems = createSearchItems(options, "option");

  // Combine and sort all search items
  const allSearchItems = sortByTermLength([
    ...categoryItems,
    ...searchedCategoryItems,
    ...optionItems,
  ]);

  // Find all matches in the query
  const matches = findAllMatches(query, allSearchItems);

  // Create the final token array with text segments
  return createTokensWithTextSegments(query, matches);
}
